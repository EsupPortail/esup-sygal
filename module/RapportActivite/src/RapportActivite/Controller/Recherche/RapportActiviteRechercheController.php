<?php

namespace RapportActivite\Controller\Recherche;

use Application\Controller\AbstractController;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\Role;
use Application\Exporter\ExporterDataException;
use Application\Filter\IdifyFilter;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\WhereSearchFilter;
use Application\Search\SearchServiceAwareTrait;
use Fichier\Entity\FichierArchivable;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceAwareTrait;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use RapportActivite\Service\Search\RapportActiviteSearchService;
use RuntimeException;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnexpectedValueException;
use UnicaenPrivilege\Provider\Privilege\Privileges;

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
        $this->restrictFilters();

        $text = $this->params()->fromQuery('text');

        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $operationss = [];
        foreach ($paginator as $rapport) {
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
        $this->restrictFilters();

        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }

    private function restrictFilters(): void
    {
        $this->restrictFilterDoctorant();
        $this->restrictFilterEtablissementTheseSearchFilter();
        $this->restrictFilterEcolesDoctorales();
        $this->restrictFilterUnitesRecherches();
        $this->restrictFilterNomDirecteur();
    }


    private function restrictFilterDoctorant(): void
    {
        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            $role = $this->userContextService->getSelectedIdentityRole();
            if ($role->isDirecteurThese() || $role->isCodirecteurThese()) {
                $this->searchService->addFilter((new WhereSearchFilter("invisible", 'acteur_individu'))
                    ->setWhereField('act.individu')
                    ->setDefaultValue($this->userContextService->getDbUser()->getIndividu()->getId())
                    ->setVisible(false)
                );
                $this->searchService->addFilter((new WhereSearchFilter("invisible", 'acteur_role'))
                    ->setWhereField('actr.code')
                    ->setDefaultValue($role->isDirecteurThese() ? Role::CODE_DIRECTEUR_THESE : Role::CODE_CODIRECTEUR_THESE)
                    ->setVisible(false)
                );
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }

    }

    private function restrictFilterEcolesDoctorales(): void
    {
        $filter = $this->searchService->getEcoleDoctoraleSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
                $filter->setData([$ed]);
                $filter->setDefaultValueAsObject($ed);
                $filter->setAllowsEmptyOption(false);
            } elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
                $filter->setVisible(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
    }

    private function restrictFilterEtablissementTheseSearchFilter(): void
    {
        $filter = $this->searchService->getEtablissementTheseSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            $role = $this->userContextService->getSelectedIdentityRole();
            if ($etab = $role->getStructure()?->getEtablissement()) {
                $filter->setData([$etab]);
                $filter->setDefaultValueAsObject($etab);
                $filter->setAllowsEmptyOption(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
    }

    private function restrictFilterUnitesRecherches(): void
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
            } elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
                $filter->setVisible(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
    }

    private function restrictFilterNomDirecteur(): void
    {
        $filter = $this->searchService->getNomDirecteurSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT))) {
            // aucune restriction
        } elseif ($this->isAllowed(Privileges::getResourceId(RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN))) {
            // restrictions en fonction du rôle
            if ($this->userContextService->getSelectedRoleDirecteurThese()) {
                $filter->setVisible(false);
            }
        } else {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT, RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN])
            );
        }
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

        return $result;
    }

    /**
     * @return void|Response
     */
    public function telechargerZipAction(): Response
    {
        $this->restrictFilters();

        $result = $this->search();
        if ($result instanceof Response) {
            return $result; // théoriquement, on ne devrait pas arriver ici.
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $fichiersArchivables = [];
        /** @var RapportActivite $rapport */
        foreach ($paginator as $rapport) {
            // pour l'instant on zappe les rapports dématérialisés (doute sur le temps de réponse de la génération PDF préalable)
            if ($rapport->getFichier() === null) {
                continue;
            }

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

        if (!count($fichiersArchivables)) {
            $this->flashMessenger()->addErrorMessage("Aucun rapport à télécharger ou rapports non téléchargeables au format ZIP.");
            return $this->redirect()->toRoute('rapport-activite/recherche/index', [], ['query' => $this->params()->fromQuery()], true);
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