<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
use Application\Search\SearchServiceInterface;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;

interface SearchControllerInterface
{
    /**
     * @param SearchServiceInterface $searchService
     * @return void
     */
    public function setSearchService(SearchServiceInterface $searchService);

    /**
     * @return SearchServiceInterface
     */
    public function getSearchService(): SearchServiceInterface;

    /**
     * @return ViewModel
     */
    public function filtersAction(): ViewModel;

    /**
     * @return Response|LaminasPaginator
     */
    public function search();

    /**
     * @return SearchFilter[]
     */
    public function filters(): array;
}