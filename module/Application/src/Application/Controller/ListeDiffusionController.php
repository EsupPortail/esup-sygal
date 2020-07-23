<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\ListeDiffusion\ListeDiffusionServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Webmozart\Assert\Assert;
use Zend\View\Model\ViewModel;

class ListeDiffusionController extends AbstractController
{
    use ListeDiffusionServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use FileServiceAwareTrait;
    use NotifierServiceAwareTrait;

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

        $listesDeclarees = $this->listeDiffusionService->fetchListesDiffusion();
        Assert::inArray($this->liste, $listesDeclarees, "Liste spécifiée non déclarée.");

        $this->listeDiffusionService->setListe($this->liste);
        $this->listeDiffusionService->init();

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
     * Dépouillage des paramètres de la requête.
     *
     * Les paramètres de routage acceptés sont les suivants :
     *   - `liste` (OBLIGATOIRE) : cf. {@see $liste}.
     */
    private function loadRequestParams()
    {
        $this->liste = $this->getRequestedListe(); // ex: 'ed591.doctorants.insa@normandie-univ.fr'
    }

    /**
     * Génération du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le fichier retourné contient une adresse électronique par ligne.
     */
    public function generateMemberIncludeAction()
    {
        $this->loadRequestParams();

        $this->listeDiffusionService->setListe($this->liste);
        $this->listeDiffusionService->init();

        $content = $this->listeDiffusionService->createMemberIncludeFileContent();
        //$this->handleMemberIncludeNotFoundEmails(); // PAS POSSIBLE : Sympa interroge toutes les heures !

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

        $this->listeDiffusionService->setListe($this->liste);
        $this->listeDiffusionService->init();

        $content = $this->listeDiffusionService->createOwnerIncludeFileContent();

        $filename = $this->listeDiffusionService->generateResultFileName('owner');
        $this->fileService->downloadFileFromContent($content, $filename);
    }

    /**
     * Gestion des ABONNÉS sans adresse mail.
     */
    private function handleMemberIncludeNotFoundEmails()
    {
        $individusSansAdresse = $this->listeDiffusionService->getIndividusSansAdresse();
        if (empty($individusSansAdresse)) {
            return;
        }

        $individusAvecAdresse = $this->listeDiffusionService->getIndividusAvecAdresse();

        // Envoi d'une notif aux admin tech
        $to = $this->fetchAdminTechEmails();
        $this->notifierService->triggerAbonnesListeDiffusionSansAdresse(
            $to,
            $this->liste,
            $individusAvecAdresse,
            $individusSansAdresse);
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

    /**
     * @return string[]
     */
    private function fetchAdminTechEmails()
    {
        $individus = $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);

        return array_map(function(Individu $i) { return $i->getEmailUtilisateur(); }, $individus);
    }
}