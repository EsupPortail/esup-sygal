<?php

namespace Application\Controller;

use Application\Entity\Db\ListeDiffusion;
use Application\Entity\Db\Role;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressGenerator;
use Application\Service\ListeDiffusion\ListeDiffusionServiceAwareTrait;
use Application\Service\ListeDiffusion\Url\UrlServiceAwareTrait;
use Application\Service\Notification\ApplicationNotificationFactoryAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use SplObjectStorage;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\View\Model\CsvModel;
use Webmozart\Assert\Assert;

class ListeDiffusionController extends AbstractController
{
    use ListeDiffusionServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use ApplicationNotificationFactoryAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use UrlServiceAwareTrait;

    private ListeDiffusion $liste;

    private SplObjectStorage $dataByEtablissement;

    /**
     * @var ListeDiffusion[] Format : 'adresse' => ListeDiffusion
     */
    private array $listesDiffusionActives = [];

    /**
     * @var string[] Format : 'adresse' => 'adresse'
     */
    private array $adressesGenerees = [];

    public function indexAction(): Response|ViewModel
    {
        $etablissement = $this->params()->fromQuery('etablissement');

        if ($this->params()->fromPost()) {
            return $this->modifierListes();
        }

        /** @var EcoleDoctorale[] $ecolesDoctorales */
        $etablissements = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
        $ecolesDoctorales = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);
        $codesRolesAvecTemoinsED = [
            Role::CODE_DOCTORANT => true,
            Role::CODE_DIRECTEUR_THESE => true,
//            Role::CODE_BU => false,
//            Role::CODE_BDD => false,
//            Role::CODE_ADMIN_TECH => false, // NB: pas de structure liée
        ];
        $roles = $this->applicationRoleService->getRepository()->findByCodes(array_keys($codesRolesAvecTemoinsED));

        $etablissementsAsStructures = array_map(fn(Etablissement $e) => $e->getStructure(), $etablissements);
        $roles = array_filter($roles, fn(Role $role) =>
            $role->getStructure() === null || // ex: Admin tech
            in_array($role->getStructure(), $etablissementsAsStructures)
        );
        $this->prepareDataForView($etablissements, $ecolesDoctorales, $roles, $codesRolesAvecTemoinsED);
        $this->loadListesDiffusionActives();

        $adressesListesActives = array_keys($this->listesDiffusionActives);
        $adressesListesActivesMaisInexistanteDansGenerees = array_diff($adressesListesActives, $this->adressesGenerees);

        return new ViewModel([
            'codeEtablissement' => $etablissement,
            'dataByEtablissement' => $this->dataByEtablissement,
            'listesDiffusionActives' => $this->listesDiffusionActives,
            'adressesListesActivesMaisInexistanteDansGenerees' => $adressesListesActivesMaisInexistanteDansGenerees,
            'urlSympa' => $this->listeDiffusionService->getUrlSympa(),
        ]);
    }

    /**
     * Fetche en bdd les listes de diffusion actives.
     * NB: pour l'instant le table LISTE_DIFF ne contient que les listes activées.
     */
    private function loadListesDiffusionActives(): void
    {
        $this->listesDiffusionActives = [];
        $listesDiffusionActives = $this->listeDiffusionService->fetchListesDiffusionActives();
        foreach ($listesDiffusionActives as $listeDiffusion) {
            $this->listesDiffusionActives[$listeDiffusion->getAdresse()] = $listeDiffusion;
        }
    }

    private function modifierListes(): Response
    {
        $post = $this->params()->fromPost();
        $etablissement = $post['etablissement'] ?? 'Tous';
        $adresses = $post['listes'] ?? [];
        try {
            $this->enregistrer((array) $adresses, $etablissement);
        } catch (ORMException $e) {
            $this->flashMessenger()->addErrorMessage("Erreur rencontrée lors de l'enregistrement des listes : " . $e->getMessage());
            error_log($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return $this->redirect()->toRoute(null, [], ['query' => ['etablissement' => $etablissement]], true);
    }

    /**
     * @param string[] $adresses
     * @param string $etablissement
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    private function enregistrer(array $adresses, string $etablissement): void
    {
        $adressesToDelete = array_filter($adresses, function (string $checked) { return $checked === '0'; });
        $adressesToInsert = array_filter($adresses, function (string $checked) { return $checked === '1'; });

        if ($adressesToDelete) {
            $this->listeDiffusionService->deleteListesDiffusions(array_keys($adressesToDelete));
        }

        $listes = [];
        foreach (array_keys($adressesToInsert) as $adresse) {
            $liste = $this->listeDiffusionService->findListeDiffusionByAdresse($adresse);
            $data = [
                'adresse' => $adresse,
                'enabled' => true,
            ];
            if ($liste === null) {
                $liste = $this->listeDiffusionService->createListeDiffusion($data);
            } else {
                $this->listeDiffusionService->updateListeDiffusion($liste, $data);
            }
            $listes[$adresse] = $liste;
        }

        $this->listeDiffusionService->saveListesDiffusions($listes);

        $this->flashMessenger()->addSuccessMessage(
            sprintf("Enregistrement effectué avec succès pour '%s' : %d adresses actives, %d inactives.",
                $etablissement, count($adressesToInsert), count($adressesToDelete)));
    }

    /**
     * @param Etablissement[] $etablissements
     * @param EcoleDoctorale[] $ecolesDoctorales
     * @param Role[] $roles
     * @param array $codesRolesAvecTemoinsED
     */
    private function prepareDataForView(array $etablissements, array $ecolesDoctorales, array $roles, array $codesRolesAvecTemoinsED): void
    {
        $this->adressesGenerees = [];

        // pour tests :
        //$etablissements = array_slice($etablissements, -1);

        //
        // Par établissement.
        //
        $dataByEtablissement = new SplObjectStorage();
        foreach ($etablissements as $etablissement) {
            $rolesForEtablissement = array_filter($roles, function (Role $r) use ($etablissement) {
                return $r->getStructure() === $etablissement->getStructure();
            });
            $dataByRole = new SplObjectStorage();
            foreach ($rolesForEtablissement as $role) {
                if ($codesRolesAvecTemoinsED[$role->getCode()] === true) {
                    $dataByED = new SplObjectStorage();
                    // d'abord, toute ED confondue
                    $edTouteConfondue = $this->ecoleDoctoraleService->createTouteEcoleDoctoraleConfondue();
                    $ng = $this->listeDiffusionService->createNameGenerator($edTouteConfondue, $role, $role->getStructure());
                    $dataByED->attach($edTouteConfondue, $this->prepareListeDataForView($ng));
                    // ensuite, par ED
                    foreach ($ecolesDoctorales as $ed) {
                        $ng = $this->listeDiffusionService->createNameGenerator($ed, $role, $role->getStructure());
                        $dataByED->attach($ed, $this->prepareListeDataForView($ng));
                    }
                    $dataByRole->attach($role, $dataByED);
                } else {
                    // hors ED
                    $ng = $this->listeDiffusionService->createNameGenerator(null, $role, $role->getStructure());
                    $dataByRole->attach($role, $this->prepareListeDataForView($ng));
                }
            }
            $dataByEtablissement->attach($etablissement, $dataByRole);
        }
        $rolesAvecTemoinsED = array_filter($roles, function (Role $r) use ($codesRolesAvecTemoinsED) {
            return $codesRolesAvecTemoinsED[$r->getCode()] === true;
        });

        if (count($etablissements) > 1) {
            //
            // Tous établissements confondus.
            //
            /** @var Role $rolePrec */
            $rolePrec = null;
            $dataByRole = new SplObjectStorage();
            foreach ($rolesAvecTemoinsED as $role) {
                if ($rolePrec && ($isSameRoleAgain = $rolePrec->getCode() === $role->getCode())) {
                    continue; // on ne retient qu'un rôle pour tous les établissements confondus
                }
                $dataByED = new SplObjectStorage();
                // d'abord, toute ED confondue
                $edTouteConfondue = $this->ecoleDoctoraleService->createTouteEcoleDoctoraleConfondue();
                $ng = $this->listeDiffusionService->createNameGenerator($edTouteConfondue, $role, null);
                $dataByED->attach($edTouteConfondue, $this->prepareListeDataForView($ng));
                // ensuite, par ED
                foreach ($ecolesDoctorales as $ed) {
                    $ng = $this->listeDiffusionService->createNameGenerator($ed, $role, null);
                    $dataByED->attach($ed, $this->prepareListeDataForView($ng));
                }
                $dataByRole->attach($role, $dataByED);
                $rolePrec = $role;
            }
            $etablissementToutConfondu = $this->etablissementService->createToutEtablissementConfondu();
            $dataByEtablissement->attach($etablissementToutConfondu, $dataByRole);
        }

        // NB: $this->adressesGenerees a été peuplé.

        $this->dataByEtablissement = $dataByEtablissement;
    }

    private function prepareListeDataForView(ListeDiffusionAddressGenerator $namer): array
    {
        $domain = $this->listeDiffusionService->getEmailDomain();
        $namer->setDomain($domain);
        try {
            $name = $namer->generateName();
            $link = $this->url()->fromRoute('liste-diffusion/liste', ['adresse' => $name]);
            $enabled = true;
        } catch (InvalidArgumentException $e) {
            $name = "Anomalie rencontrée : " . $e->getMessage();
            $link = null;
            $enabled = false;
        }

        // collecte avec indexation par adresse
        $this->adressesGenerees[$name] = $name;

        return [
            'name' => $name,
            'label' => $name,
            'link' => $link,
            'enabled' => $enabled,
        ];
    }

    public function consulterAction(): ViewModel
    {
        $this->loadRequestParams();

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
            'generateMemberIncludeUrl' => $this->urlService->generateMemberIncludeUrl($this->liste),
            'generateOwnerIncludeUrl' => $this->urlService->generateOwnerIncludeUrl($this->liste),
        ]);
    }

    /**
     * Dépouillage des paramètres de la requête.
     *
     * Les paramètres de routage acceptés sont les suivants :
     *   - `liste` (OBLIGATOIRE) : cf. {@see $liste}.
     */
    private function loadRequestParams(): void
    {
        $this->liste = $this->getRequestedListe(); // ex: 'ed591.doctorants.insa@normandie-univ.fr'
    }

    /**
     * Génération du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le fichier retourné contient une adresse électronique par ligne.
     */
    public function generateMemberIncludeAction(): void
    {
        $this->loadRequestParams();

        $this->listeDiffusionService->setListe($this->liste);
        $this->listeDiffusionService->init();

        $content = $this->listeDiffusionService->createMemberIncludeFileContent();
        //$this->handleMemberIncludeNotFoundEmails(); // PAS POSSIBLE : Sympa interroge toutes les heures !

        $filename = $this->listeDiffusionService->generateResultFileName('member');
        FileUtils::downloadFileFromContent($content, $filename);
    }

    /**
     * Génération du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le fichier retourné contient une adresse électronique par ligne.
     */
    public function generateOwnerIncludeAction(): void
    {
        $this->loadRequestParams();

        $this->listeDiffusionService->setListe($this->liste);
        $this->listeDiffusionService->init();

        $content = $this->listeDiffusionService->createOwnerIncludeFileContent();

        $filename = $this->listeDiffusionService->generateResultFileName('owner');
        FileUtils::downloadFileFromContent($content, $filename);
    }

    public function exporterTableauAction(): CsvModel
    {
        $data = $this->listeDiffusionService->createDataForCsvExport($this->url());
        $header = current($data);
        $data = array_slice($data, 1);

        $model = new CsvModel();
        $model->setDelimiter(';');
        $model->setEnclosure('"');
        $model->setHeader($header);
        $model->setData($data);
        $model->setFilename(sprintf('sygal_listediff_sympa_%s.csv', date_create('now')->format("Ymd-His")));

        return $model;
    }

    /**
     * Gestion des ABONNÉS sans adresse mail.
     */
    private function handleMemberIncludeNotFoundEmails(): void
    {
        $individusSansAdresse = $this->listeDiffusionService->getIndividusSansAdresse();
        if (empty($individusSansAdresse)) {
            return;
        }

        $individusAvecAdresse = $this->listeDiffusionService->getIndividusAvecAdresse();

        // Envoi d'une notif aux admin tech
        $to = $this->fetchAdminTechEmails();
        $notif = $this->applicationNotificationFactory->createNotificationAbonnesListeDiffusionSansAdresse(
            $to,
            $this->liste,
            $individusAvecAdresse,
            $individusSansAdresse);
        $this->notifierService->trigger($notif);
    }

    /**
     * Gestion des PROPRÉTAIRES sans adresse mail.
     */
    private function handleOwnerIncludeNotFoundEmails(): void
    {

    }

    private function getRequestedListe(): ListeDiffusion
    {
        $adresse = $this->params()->fromRoute('adresse');
        Assert::notNull($adresse, "Aucune adresse spécifiée.");

        /** @var ListeDiffusion $liste */
        $liste = $this->listeDiffusionService->getRepository()->findOneBy(['adresse' => $adresse]);
        Assert::notNull($liste, "Aucune liste active trouvée avec l'adresse spécifiée.");

        return $liste;
    }

    /**
     * @return string[]
     */
    private function fetchAdminTechEmails(): array
    {
        $individus = $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);

        return array_map(function(Individu $i) { return $i->getEmailUtilisateur(); }, $individus);
    }
}