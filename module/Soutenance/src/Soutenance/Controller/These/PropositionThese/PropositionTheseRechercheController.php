<?php

namespace Soutenance\Controller\These\PropositionThese;

use Application\Controller\AbstractController;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseSearchService;

/**
 * @property PropositionTheseSearchService $searchService
 */
class PropositionTheseRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    /**
     * @var array
     */
    private array $queryParams = [];

    /**
     * @var array
     */
    private $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $this->restrictFilters();

        /** @see PropositionTheseSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition-these/recherche/index');
        $vm->setVariables([
            'propositions' => $paginator,
            'role' => $this->userContextService->getSelectedIdentityRole(),
        ]);
        return $vm;
    }

    /**
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictFilters();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
        ]);
        $vm->setTemplate('soutenance/proposition-recherche/filters');

        return $vm;
    }

    private function restrictFilters()
    {
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