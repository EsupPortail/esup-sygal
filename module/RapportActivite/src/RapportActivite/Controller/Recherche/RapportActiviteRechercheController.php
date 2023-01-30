<?php

namespace RapportActivite\Controller\Recherche;

use Application\Controller\AbstractController;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Exporter\ExporterDataException;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Fichier\Entity\FichierArchivable;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Rule\Avis\RapportActiviteAvisRuleAwareTrait;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use RuntimeException;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnexpectedValueException;
use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * @property \RapportActivite\Service\Search\RapportActiviteSearchService $searchService
 */
class RapportActiviteRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use StructureServiceAwareTrait;
    use FichierServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteAvisRuleAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;
    use RapportActiviteFichierServiceAwareTrait;

    use TypeValidationAwareTrait;

    protected string $routeName = 'rapport-activite';

    protected string $title = "Rapports d'activité";


    /**
     * @var string
     */
    protected string $indexActionTemplate = 'rapport-activite/rapport-recherche/index';
    protected string $filtersActionTemplate = 'rapport-activite/rapport-recherche/filters';

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $this->restrictFilterEcolesDoctorales();
        $this->restrictFilterUnitesRecherches();
        $this->initFilterAvisAttendu();

        $text = $this->params()->fromQuery('text');

        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $operationss = [];
        foreach ($paginator as $rapport) {
//            $this->rapportActiviteAvisRule->injectRapportAvisPossible($rapport);
//            $this->rapportActiviteOperationRule->injectOperationPossible($rapport);
            $operationss[$rapport->getId()] = $this->rapportActiviteOperationRule->getOperationsForRapport($rapport);
        }

        $model = new ViewModel([
            'title' => $this->title,
            'paginator' => $paginator,
            'text' => $text,

            'operationss' => $operationss,
            'typeValidation' => $this->typeValidation,
            'routeName' => $this->routeName,

            'returnUrl' => $this->getRequest()->getRequestUri(),

            'displayEtablissement' => true,
            'displayType' => true,
            'displayDoctorant' => true,
            'displayDirecteurThese' => true,
            'displayEcoleDoctorale' => true,
            'displayUniteRecherche' => true,
            'displayAvis' => true,
            'displayValidation' => true,
        ]);
        $model->setTemplate($this->indexActionTemplate);

        return $model;
    }

    /**
     * Surcharge de la méthode {@see SearchControllerTrait::filtersAction()}.
     *
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictFilterEcolesDoctorales();
        $this->restrictFilterUnitesRecherches();
        $this->initFilterAvisAttendu();

        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }

    private function restrictFilterEcolesDoctorales()
    {
        $edFilter = $this->searchService->getEcoleDoctoraleSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
                $edFilter->setData([$ed]);
                $edFilter->setDefaultValueAsObject($ed);
                $edFilter->setAllowsEmptyOption(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
    }

    private function restrictFilterUnitesRecherches()
    {
        $filter = $this->searchService->getUniteRechercheSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            if ($roleUniteRecherche = $this->userContextService->getSelectedRoleUniteRecherche()) {
                $ur = $roleUniteRecherche->getStructure()->getUniteRecherche();
                $filter->setData([$ur]);
                $filter->setDefaultValueAsObject($ur);
                $filter->setAllowsEmptyOption(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
    }

    /**
     * Initialisations du filtre "Avis attendu".
     */
    private function initFilterAvisAttendu()
    {
//        $filter = $this->searchService->getAvisManquantSearchFilter();
//
//        /**
//         * Valeur par défaut (NB : empêche de sélectionner la valeur "Peu importe") :
//         *   - pour le rôle Gestionnaire d'ED : "Avis gestionnaire d'ED"
//         *   - pour le rôle Responsable d'ED : "Avis direction d'ED"
//         */
//        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
//            if ($roleEcoleDoctorale->getCode() === Role::CODE_GEST_ED) {
//                $filter->setDefaultValue(RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST);
//            } elseif ($roleEcoleDoctorale->getCode() === Role::CODE_RESP_ED) {
//                $filter->setDefaultValue(RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR);
//            }
//        }
//
//        /**
//         * Valeur par défaut (NB : empêche de sélectionner la valeur "Peu importe") :
//         *   - pour le rôle Observateur COMUE : "Avis gestionnaire d'ED"
//         */
//        if ($this->userContextService->getSelectedIdentityRole()->getCode() === Role::CODE_OBSERVATEUR_COMUE) {
//            $filter->setDefaultValue(RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST);
//        }
    }

    /**
     * Redéfinition de la méthode {@see SearchControllerTrait::filtersAction()}
     * pour injecter des choses dans les rapports d'activité avant affichage.
     *
     * @return Response|LaminasPaginator
     */
    public function search()
    {
        $result = $this->getSearchPluginController()->search();
        if ($result instanceof Response) {
            return $result;
        }

        $result->setItemCountPerPage(25);

        /** @var RapportActivite $rapport */
        foreach ($result as $rapport) {
            $this->rapportActiviteAvisRule->injectRapportAvisPossible($rapport);
        }

        return $result;
    }

    /**
     * @return void|Response
     */
    public function telechargerZipAction(): Response
    {
        $this->restrictFilterEcolesDoctorales();

        $result = $this->search();
        if ($result instanceof Response) {
            return $result; // théoriquement, on ne devrait pas arriver ici.
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $fichiersArchivables = [];
        /** @var RapportActivite $rapport */
        foreach ($paginator as $rapport) {
            $fichierArchivable = new FichierArchivable($rapport->getFichier());
            // s'il s'agit d'un rapport validé, on ajoute à la volée la page de validation
            if ($rapport->getRapportValidationOfType($this->typeValidation)) {
                // l'ajout de la page de validation n'est pas forcément possible
                if ($rapport->supporteAjoutPageValidation()) {
                    try {
                        $exportData = $this->rapportActiviteService->createPageValidationDataForRapport($rapport);
                    } catch (ExporterDataException $e) {
                        $redirect = $this->params()->fromQuery('redirect');
                        $this->flashMessenger()->addErrorMessage(sprintf(
                            "Impossible de générer la page de validation du rapport '%s'. " . $e->getMessage(),
                            $rapport->getFichier()->getNom()
                        ));
                        return $redirect ?
                            $this->redirect()->toUrl($redirect) :
                            $this->redirect()->toRoute($this->routeName . '/recherche/index');
                    }
                    $outputFilePath = $this->rapportActiviteFichierService->createFileWithPageValidation($rapport, $exportData);
                    $fichierArchivable->setFilePath($outputFilePath);
                }
            }
            $fichierArchivable->setFilePathInArchive($rapport->generateInternalPathForZipArchive());
            $fichiersArchivables[] = $fichierArchivable;
        }

        $filename = sprintf("sygal_%s.zip", strtolower(RapportActivite::CODE));
        try {
            $fichierZip = $this->fichierService->compresserFichiers($fichiersArchivables, $filename);
        } catch (FichierServiceException $e) {
            throw new RuntimeException("Une erreur est survenue empêchant la création de l'archive zip", null, $e);

        }
        $this->fichierService->telechargerFichier($fichierZip);
    }
}