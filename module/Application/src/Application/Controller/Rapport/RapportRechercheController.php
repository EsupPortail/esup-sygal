<?php

namespace Application\Controller\Rapport;

use Application\Controller\AbstractController;
use Application\Entity\Db\Interfaces\TypeRapportAwareTrait;
use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\Rapport;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Rapport\RapportSearchService;
use Fichier\Entity\FichierArchivable;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierServiceException;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

/**
 * @property RapportSearchService $searchService
 */
abstract class RapportRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;
    
    use StructureServiceAwareTrait;
    use FichierServiceAwareTrait;
    use TypeRapportAwareTrait;
    use TypeValidationAwareTrait;

    /**
     * @var string À redéfinir dans les sous-classes !
     */
    protected $routeName;

    /**
     * @var string À redéfinir dans les sous-classes !
     */
    protected $privilege_LISTER_TOUT;
    protected $privilege_LISTER_SIEN;
    protected $privilege_TELEVERSER_TOUT;
    protected $privilege_TELEVERSER_SIEN;
    protected $privilege_SUPPRIMER_TOUT;
    protected $privilege_SUPPRIMER_SIEN;
    protected $privilege_RECHERCHER_TOUT;
    protected $privilege_RECHERCHER_SIEN;
    protected $privilege_TELECHARGER_TOUT;
    protected $privilege_TELECHARGER_SIEN;
    protected $privilege_TELECHARGER_ZIP;
    protected $privilege_VALIDER_TOUT;
    protected $privilege_VALIDER_SIEN;
    protected $privilege_DEVALIDER_TOUT;
    protected $privilege_DEVALIDER_SIEN;

    /**
     * @var string À redéfinir dans les sous-classes !
     */
    protected $title;

    /**
     * @var string
     */
    protected $indexActionTemplate = 'application/rapport/rapport-recherche/index';
    protected $filtersActionTemplate = 'application/rapport/rapport-recherche/filters';

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $this->restrictEcolesDoctorales();
        $this->restrictUnitesRecherches();

        $text = $this->params()->fromQuery('text');

        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $model = new ViewModel([
            'title' => $this->title,
            'paginator' => $paginator,
            'text' => $text,

            'typeRapport' => $this->typeRapport,
            'typeValidation' => $this->typeValidation,
            'routeName' => $this->routeName,
            'privilege_LISTER_TOUT' => $this->privilege_LISTER_TOUT,
            'privilege_LISTER_SIEN' => $this->privilege_LISTER_SIEN,
            'privilege_TELEVERSER_TOUT' => $this->privilege_TELEVERSER_TOUT,
            'privilege_TELEVERSER_SIEN' => $this->privilege_TELEVERSER_SIEN,
            'privilege_SUPPRIMER_TOUT' => $this->privilege_SUPPRIMER_TOUT,
            'privilege_SUPPRIMER_SIEN' => $this->privilege_SUPPRIMER_SIEN,
            'privilege_RECHERCHER_TOUT' => $this->privilege_RECHERCHER_TOUT,
            'privilege_RECHERCHER_SIEN' => $this->privilege_RECHERCHER_SIEN,
            'privilege_TELECHARGER_TOUT' => $this->privilege_TELECHARGER_TOUT,
            'privilege_TELECHARGER_SIEN' => $this->privilege_TELECHARGER_SIEN,
            'privilege_TELECHARGER_ZIP' => $this->privilege_TELECHARGER_ZIP,
            'privilege_VALIDER_TOUT' => $this->privilege_VALIDER_TOUT,
            'privilege_VALIDER_SIEN' => $this->privilege_VALIDER_SIEN,
            'privilege_DEVALIDER_TOUT' => $this->privilege_DEVALIDER_TOUT,
            'privilege_DEVALIDER_SIEN' => $this->privilege_DEVALIDER_SIEN,

            'returnUrl' => $this->getRequest()->getRequestUri(),

            'displayEtablissement' => true,
            'displayType' => true,
            'displayDoctorant' => true,
            'displayDirecteurThese' => true,
            'displayEcoleDoctorale' => true,
            'displayUniteRecherche' => true,
            'displayValidation' => false,
        ]);
        $model->setTemplate($this->indexActionTemplate);

        return $model;
    }

    /**
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictEcolesDoctorales();
        $this->restrictUnitesRecherches();

        $this->searchService->getValidationSearchFilter()->setVisible(false);

        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }

    private function restrictEcolesDoctorales()
    {
        $edFilter = $this->searchService->getEcoleDoctoraleSearchFilter();
        $protoRapport = new Rapport($this->typeRapport);

        if ($this->isAllowed($protoRapport, $this->privilege_LISTER_TOUT)) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed($protoRapport, $this->privilege_LISTER_SIEN)) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
                $edFilter->setData([$ed]);
                $edFilter->setDefaultValueAsObject($ed);
                $edFilter->setAllowsEmptyOption(false);
            }
        }
    }

    private function restrictUnitesRecherches()
    {
        $filter = $this->searchService->getUniteRechercheSearchFilter();
        $protoRapport = new Rapport($this->typeRapport);

        if ($this->isAllowed($protoRapport, $this->privilege_LISTER_TOUT)) {
            // aucune restriction sur les UR sélectionnables
        } elseif ($this->isAllowed($protoRapport, $this->privilege_LISTER_SIEN)) {
            // restrictions en fonction du rôle
            if ($role = $this->userContextService->getSelectedRoleUniteRecherche()) {
                $ur = $role->getStructure()->getUniteRecherche();
                $filter->setData([$ur]);
                $filter->setDefaultValueAsObject($ur);
                $filter->setAllowsEmptyOption(false);
            }
        }
    }

    /**
     * @return void|Response
     */
    public function telechargerZipAction(): Response
    {
        $this->restrictEcolesDoctorales();
        $this->restrictUnitesRecherches();

        $result = $this->search();
        if ($result instanceof Response) {
            return $result; // théoriquement, on ne devrait pas arriver ici.
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $fichiersArchivables = [];
        /** @var Rapport $rapport */
        foreach ($paginator as $rapport) {
            $fichierArchivable = new FichierArchivable($rapport->getFichier());
            $fichierArchivable->setFilePathInArchive($rapport->generateInternalPathForZipArchive());
            $fichiersArchivables[] = $fichierArchivable;
        }

        $filename = sprintf("sygal_%s.zip", strtolower($this->typeRapport->getCode()));
        try {
            $fichierZip = $this->fichierService->compresserFichiers($fichiersArchivables, $filename);
        } catch (FichierServiceException $e) {
            throw new RuntimeException("Une erreur est survenue empêchant la création de l'archive zip", null, $e);
        }
        $this->fichierService->telechargerFichier($fichierZip);
    }
}