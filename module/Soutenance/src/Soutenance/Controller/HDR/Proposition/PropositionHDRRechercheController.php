<?php

namespace Soutenance\Controller\HDR\Proposition;

use Application\Controller\AbstractController;
use UnicaenSearch\Controller\SearchControllerInterface;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchResultPaginator;
use UnicaenSearch\SearchServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRSearchService;

/**
 * @property PropositionHDRSearchService $searchService
 */
class PropositionHDRRechercheController extends AbstractController implements SearchControllerInterface
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

        /** @see PropositionHDRSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/proposition-hdr/recherche/index');
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
}