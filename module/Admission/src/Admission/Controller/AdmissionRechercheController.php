<?php

namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\TypeValidation;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionRechercheService;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Operation\AdmissionOperationServiceAwareTrait;
use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Form\Element\SearchAndSelect;
use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Class AdmissionRechercheController
 *
 * @property AdmissionRechercheService $searchService
 */
class AdmissionRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use AdmissionServiceAwareTrait;
    use AdmissionOperationRuleAwareTrait;
    use AdmissionOperationServiceAwareTrait;

    /**
     * @var array
     */
    private $queryParams;

    /**
     * @var array
     */
    private $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    protected string $routeName = 'admission';

    /**
     * @var string
     */
    protected string $indexActionTemplate = 'admission/admission-recherche/index';
    protected string $filtersActionTemplate = 'admission/admission-recherche/filters';

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $individu = $request->getPost('individuId');
            //Redirige vers le dossier d'admission de l'individu, si un individu est renseigné
            if ($individu && $individu['id']) {
                return $this->redirect()->toRoute(
                    'admission/ajouter',
                    ['action' => "etudiant",
                        'individu' => $individu['id']],
                    [],
                    true);
            }
        }

        $inputIndividu = new SearchAndSelect();
        $inputIndividu
            ->setAutocompleteSource($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true))
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'individuId',
                'name' => 'individuId',
            ]);

        $individu = $this->userContextService->getIdentityIndividu();
        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);

        //Récupération des opérations liées au dossier d'admission
        $operations = $admission ? $this->admissionOperationRule->getOperationsForAdmission($admission) : [];
        //Masquage des actions non voulues dans le circuit de signatures -> celles correspondant à la convention de formation doctorale
        $operations = $this->admissionOperationService->hideOperations($operations, TypeValidation::CODE_VALIDATIONS_CONVENTION_FORMATION_DOCTORALE);
        $operationEnAttente = $admission ? $this->admissionOperationRule->getOperationEnAttente($admission) : null;
        $role = $this->userContextService->getSelectedIdentityRole();
        $isOperationAllowedByRole = !$operationEnAttente || $this->admissionOperationRule->isOperationAllowedByRole($operationEnAttente, $role);
        $commentaires = $admission ? $this->admissionService->getCommentaires($admission) : null;

        //---------------------------Récupération des dossiers d'admissions correspondant aux filtres spécifiés--------------
        $this->restrictFilters();

        $text = $this->params()->fromQuery('text');

        /** @see AdmissionRechercheService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        $model = new ViewModel([
            'admissions' => $paginator,
            'text' => $text,
            'routeName' => $this->routeName,
            'operations' => $operations,
            'role' => $this->userContextService->getSelectedIdentityRole(),
            'individu' => $individu,
            'admission' => $admission,
            'inputIndividu' => $inputIndividu,
            'operationEnAttente' => $operationEnAttente,
            'isOperationAllowedByRole' => $isOperationAllowedByRole,
            'commentaires' => $commentaires
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

    private function restrictFilterEcolesDoctorales()
    {
        $edFilter = $this->searchService->getEcoleDoctoraleSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(AdmissionPrivileges::ADMISSION_LISTER_MES_DOSSIERS_ADMISSION))) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
                $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
                $edFilter->setData([$ed]);
                $edFilter->setDefaultValueAsObject($ed);
                $edFilter->setAllowsEmptyOption(false);
            }
        }
    }

    private function restrictFilterUnitesRecherches()
    {
        $urFilter = $this->searchService->getUniteRechercheSearchFilter();

        if ($this->isAllowed(Privileges::getResourceId(AdmissionPrivileges::ADMISSION_LISTER_MES_DOSSIERS_ADMISSION))) {
            // restrictions en fonction du rôle
            if ($roleUniteRecherche = $this->userContextService->getSelectedRoleUniteRecherche()) {
                $ed = $roleUniteRecherche->getStructure()->getUniteRecherche();
                $urFilter->setData([$ed]);
                $urFilter->setDefaultValueAsObject($ed);
                $urFilter->setAllowsEmptyOption(false);
            }
        }
    }

    private function restrictFilters()
    {
        /** @var Role $role */
        $role = $this->userContextService->getSelectedIdentityRole();

        switch (true) {
            case $role->isEcoleDoctoraleDependant():
                $filter = $this->searchService->getEcoleDoctoraleSearchFilter();
                $entity = $role->getStructure()->getEcoleDoctorale();
                break;
            case $role->isUniteRechercheDependant():
                $filter = $this->searchService->getUniteRechercheSearchFilter();
                $entity = $role->getStructure()->getUniteRecherche();
                break;
            case $role->isEtablissementDependant():
                $filter = $this->searchService->getEtablissementInscSearchFilter();
                $entity = $role->getStructure()->getEtablissement();
                break;
            default:
                return;
        }

        $filter
            ->setData([$entity])
            ->setDefaultValueAsObject($entity)
            ->setAllowsEmptyOption(false);
    }
}