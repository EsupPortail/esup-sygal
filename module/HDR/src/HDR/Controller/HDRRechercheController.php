<?php

namespace HDR\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use UnicaenSearch\Controller\SearchControllerInterface;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchResultPaginator;
use UnicaenSearch\SearchServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRSearchService;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * @property HDRSearchService $searchService
 */
class HDRRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use ApplicationRoleServiceAwareTrait;
    use HDRServiceAwareTrait;

    private bool $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    public function indexAction(): Response|ViewModel
    {
        $role = $this->userContextService->getSelectedIdentityRole();
        if($role->getCode() === Role::CODE_ADMIN_TECH){
            $this->restrictFiltersByStructure();
            $filtersRoute = 'hdr/recherche/filters';
        }else{
            $this->restrictFilters();
            $filtersRoute = 'hdr/recherche/notres/filters';
        }

        $text = $this->params()->fromQuery('text');

        /** @see HDRSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        $etablissement = $this->searchService->getEtablissementInscSearchFilter()->getValue();
        $etatHDR = $this->searchService->getFilterValueByName(HDRSearchService::NAME_etatHDR);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('hdr/hdr-recherche/index');
        $viewModel->setVariables([
            'hdrs' => $paginator,
            'text' => $text,
            'roleGarant' => $this->applicationRoleService->getRepository()->findOneBy(['sourceCode' => Role::CODE_HDR_GARANT]),
            'displayDateSoutenance' => $etatHDR === HDR::ETAT_SOUTENUE || !$etatHDR,
            'etatHDR' => $etatHDR,
            'filtersRoute' => $filtersRoute,
        ]);
        return $viewModel;
    }

    public function indexFiltersAction(): ViewModel
    {
        $this->restrictFiltersByStructure();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('hdr/hdr-recherche/filters');

        return $vm;
    }

    /**
     * Prévu pour ED, UR, MDD.
     *
     * @return ViewModel
     */
    public function notresAction()
    {
        $this->restrictFilters();

        $this->searchIfRequired = false;

        $viewModel = $this->indexAction();
        $viewModel->setTemplate('hdr/hdr-recherche/index');
        $viewModel->setVariables([
            'filtersRoute' => 'hdr/recherche/notres/filters',
        ]);

        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    public function notresFiltersAction(): ViewModel
    {
        $this->restrictFilters();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('hdr/hdr-recherche/filters');

        return $vm;
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
            ->setAllowsEmptyValueOption(false);
    }

    private function restrictFiltersByStructure(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($role->isEcoleDoctoraleDependant()) {
            $filter = $this->searchService->getEcoleDoctoraleSearchFilter();
            $sc = $role->getStructure()->getEcoleDoctorale();
            $filter->setDefaultValueAsObject($sc);
        } elseif ($role->isUniteRechercheDependant()) {
            $filter = $this->searchService->getUniteRechercheSearchFilter();
            $sc = $role->getStructure()->getUniteRecherche();
            $filter->setDefaultValueAsObject($sc);
        } elseif ($role->isEtablissementDependant()) {
            $filter = $this->searchService->getEtablissementInscSearchFilter();
            $sc = $role->getStructure()->getEtablissement();
            $filter->setDefaultValueAsObject($sc);
        }
    }
}