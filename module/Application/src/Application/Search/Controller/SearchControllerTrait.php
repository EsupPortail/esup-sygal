<?php

namespace Application\Search\Controller;

use Application\Search\SearchServiceInterface;
use Zend\Http\Response;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\View\Model\ViewModel;

/**
 * Trait SearchControllerTrait
 *
 * @method SearchControllerPlugin searchControllerPlugin()
 */
trait SearchControllerTrait
{
    /**
     * @var SearchServiceInterface
     */
    protected $searchService;

    /**
     * @param SearchServiceInterface $searchService
     */
    public function setSearchService(SearchServiceInterface $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * @return Response|ZendPaginator
     */
    public function search()
    {
        $searchControllerPlugin = $this->searchControllerPlugin();
        $searchControllerPlugin->setSearchService($this->searchService);

        return $searchControllerPlugin->search();
    }

    /**
     * @return ViewModel
     */
    public function filtersAction()
    {
        $queryParams = $this->params()->fromQuery();

        $this->searchService
            ->initFilters()
            ->processQueryParams($queryParams);

        return new ViewModel([
            'filters' => $this->searchService->getFilters(),
        ]);
    }
}