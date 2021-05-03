<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
use Application\Search\SearchServiceInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Exception\DomainException;
use Zend\Paginator\Paginator as ZendPaginator;

/**
 * Class SearchControllerPlugin
 */
class SearchControllerPlugin extends AbstractPlugin
{
    /**
     * @return Response|ZendPaginator
     */
    public function search()
    {
        $queryParams = array_filter($this->getController()->params()->fromQuery());

        $searchService = $this->getSearchService();
        $searchService->init();

        // Application des filtres et tris par défaut, puis redirection éventuelle
        $filtersUpdated = $searchService->updateQueryParamsWithDefaultFilters($queryParams);
        $sortersUpdated = $searchService->updateQueryParamsWithDefaultSorters($queryParams);
        if ($filtersUpdated || $sortersUpdated) {
            return $this->getController()->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $searchService
            ->initFiltersWithUnpopulatedOptions()
//            ->createSorters()
            ->processQueryParams($queryParams);

        /** Configuration du paginator **/
        $qb = $searchService->getQueryBuilder();
        $maxi = $this->getController()->params()->fromQuery('maxi', 50);
        $page = $this->getController()->params()->fromQuery('page', 1);
        $paginator = new ZendPaginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage((int)$maxi)
            ->setCurrentPageNumber((int)$page);

        return $paginator;
    }

    /**
     * @return SearchFilter[]
     */
    public function filters(): array
    {
        $queryParams = $this->getController()->params()->fromQuery();

        $searchService = $this->getSearchService();
        $searchService->init();
        $searchService
            ->initFilters()
            ->processQueryParams($queryParams);

        return $searchService->getFilters();
    }

    /**
     * @return SearchControllerInterface|AbstractController
     */
    public function getController()
    {
        $controller = parent::getController();
        if (! $controller || ! $controller instanceof SearchControllerInterface) {
            throw new DomainException('Ce plugin nécessite que le contrôleur implémente ' . SearchControllerInterface::class);
        }

        return $controller;
    }

    /**
     * @return SearchServiceInterface
     */
    protected function getSearchService(): SearchServiceInterface
    {
        return $this->getController()->getSearchService();
    }

}