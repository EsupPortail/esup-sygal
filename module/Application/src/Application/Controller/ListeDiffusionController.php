<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\ListeDiffusion\ListeDiffusionServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Webmozart\Assert\Assert;
use Zend\View\Model\ViewModel;

class ListeDiffusionController extends AbstractController
{
    use ListeDiffusionServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use FileServiceAwareTrait;
    use NotifierServiceAwareTrait;

    /**
     * Numéro national de l'ED concernée.
     *
     * @var string
     */
    protected $ecoleDoctorale;

    /**
     * Etablissement concerné éventuel.
     *
     * @var Etablissement
     */
    protected $etablissement = null;

    /**
     * Valeur parmi {@see ListeDiffusionService::CIBLES}.
     *
     * @var string
     */
    protected $cible;

    /**
     * Adresse complète de la liste de diffusion, ex :
     *   - ed591.doctorants.insa@normandie-univ.fr
     *   - ed591.doctorants@normandie-univ.fr
     *   - ed591.dirtheses@normandie-univ.fr
     *
     * Où :
     * - '591' est le numéro national de l'école doctorale ;
     * - 'doctorants' (ou 'dirtheses') est la "cible" ;
     * - 'insa' est le source_code unique de l'établissement en minuscules.
     *
     * @var string
     */
    protected $liste;

    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $listes = $this->listeDiffusionService->fetchListesDiffusion();

        return new ViewModel([
            'listes' => $listes,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function consulterAction()
    {
        $this->loadRequestParams();

        $this->listeDiffusionService->createMemberIncludeFileContent();
        $memberIndividusAvecAdresse = $this->listeDiffusionService->getIndividusAvecAdresse();
        $memberIndividusSansAdresse = $this->listeDiffusionService->getIndividusSansAdresse();

        $this->listeDiffusionService->createOwnerIncludeFileContent();
        $ownerIndividusAvecAdresse = $this->listeDiffusionService->getIndividusAvecAdresse();
        $ownerIndividusSansAdresse = $this->listeDiffusionService->getIndividusSansAdresse();

        return new ViewModel([
            'liste' => $this->liste,
            'memberIndividusAvecAdresse' => $memberIndividusAvecAdresse,
            'memberIndividusSansAdresse' => $memberIndividusSansAdresse,
            'ownerIndividusAvecAdresse' => $ownerIndividusAvecAdresse,
            'ownerIndividusSansAdresse' => $ownerIndividusSansAdresse,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function memberIncludeAction()
    {
        $this->loadRequestParams();

        $this->listeDiffusionService->createMemberIncludeFileContent();

        return new ViewModel([
            'liste' => $this->liste,
            'individusAvecAdresse' => $this->listeDiffusionService->getIndividusAvecAdresse()
        ]);
    }

    public function ownerIncludeAction()
    {

    }
    
    /**
     * Dépouillage des paramètres de la requête.
     *
     * Les paramètres de routage acceptés sont les suivants :
     *   - `liste` (OBLIGATOIRE) : cf. {@see $liste}.
     */
    private function loadRequestParams()
    {
        $this->liste = $this->getRequestedListe(); // ex: 'ed591.doctorants.insa@normandie-univ.fr'

        $listeElements = explode('@', $this->liste)[0]; // ex: 'ed591.doctorants.insa'
        $listeElements = explode('.', $listeElements); // ex: ['ed591', 'doctorants', 'insa']
        $ecoleDoctorale = array_shift($listeElements); // ex: 'ed591'
        $cible = array_shift($listeElements); // ex: 'doctorants'
        $etablissement = array_shift($listeElements); // ex: 'insa'

        Assert::notNull($ecoleDoctorale, "Aucun code ED spécifié.");
        Assert::regex($ecoleDoctorale, '/^(ed)\d+$/', "L'ED doit être spécifiée au format 'ed9999'");

        Assert::notNull($cible, "Aucune cible spécifiée.");

        $this->ecoleDoctorale = substr($ecoleDoctorale, 2);
        $this->cible = $cible;
        $this->etablissement = $etablissement;
        if ($etablissement !== null) {
            $this->etablissement = $this->etablissementService->getRepository()->findOneBySourceCode(strtoupper($etablissement));
        }
        // NB : on ne fetche pas l'ED dans la base de données car plusieurs enregistrements peuvent exister dans la
        //      table des ED pour un même code national. Ce code est exploiter plus tard au moment de la recherche
        //      des abonnés à une liste de diffusion.

       $this->listeDiffusionService
            ->setListe($this->liste)
            ->setEcoleDoctorale($this->ecoleDoctorale)
            ->setCible($this->cible)
            ->setEtablissement($this->etablissement);
    }

    /**
     * Génération du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le fichier retourné contient une adresse électronique par ligne.
     */
    public function generateMemberIncludeAction()
    {
        $this->loadRequestParams();

        $content = $this->listeDiffusionService->createMemberIncludeFileContent();
        $this->handleMemberIncludeNotFoundEmails();

        $filename = $this->listeDiffusionService->generateResultFileName('member');
        $this->fileService->downloadFileFromContent($content, $filename);
    }

    /**
     * Génération du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le fichier retourné contient une adresse électronique par ligne.
     */
    public function generateOwnerIncludeAction()
    {
        $this->loadRequestParams();

        $content = $this->listeDiffusionService->createOwnerIncludeFileContent();
        $this->handleOwnerIncludeNotFoundEmails();

        $filename = $this->listeDiffusionService->generateResultFileName('owner');
        $this->fileService->downloadFileFromContent($content, $filename);
    }

    /**
     * Gestion des ABONNÉS sans adresse mail.
     */
    private function handleMemberIncludeNotFoundEmails()
    {
        $individusSansAdresse = $this->listeDiffusionService->getIndividusSansAdresse();

        // Solution retenue : Envoi d'une notif aux propriétaires de la liste.
        $individus = $this->listeDiffusionService->fetchOwners();
        $ownerEmails = array_map(function(Individu $i) { return $i->getEmail(); }, $individus);
        $this->notifierService->triggerAbonnesListeDiffusionSansAdresse($ownerEmails, $this->liste, $individusSansAdresse);
    }

    /**
     * Gestion des PROPRÉTAIRES sans adresse mail.
     */
    private function handleOwnerIncludeNotFoundEmails()
    {

    }

    /**
     * @return string
     */
    private function getRequestedListe()
    {
        $liste = $this->params()->fromRoute('liste');
        Assert::notNull($liste, "Aucune liste spécifiée.");

        return $liste;
    }
}