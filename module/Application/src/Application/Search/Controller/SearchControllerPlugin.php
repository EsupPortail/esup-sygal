<?php

namespace Application\Search\Controller;

use Application\Controller\AbstractController;
use Application\Search\Filter\SearchFilter;
use Application\Search\SearchServiceAwareTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Paginator\Paginator as ZendPaginator;

/**
 * Class SearchControllerPlugin
 *
 * @method AbstractController getController()
 */
class SearchControllerPlugin extends AbstractPlugin
{
    use SearchServiceAwareTrait;

    /**
     * @return Response|ZendPaginator
     */
    public function search()
    {
        $queryParams = $this->getController()->params()->fromQuery();

        $this->searchService->init();

        // Application des filtres et tris par défaut, puis redirection éventuelle
        $filtersUpdated = $this->searchService->updateQueryParamsWithDefaultFilters($queryParams);
        $sortersUpdated = $this->searchService->updateQueryParamsWithDefaultSorters($queryParams);
        if ($filtersUpdated || $sortersUpdated) {
            return $this->getController()->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $this->searchService
            ->initFiltersWithUnpopulatedOptions()
//            ->createSorters()
            ->processQueryParams($queryParams);

        /** Configuration du paginator **/
        $qb = $this->searchService->getQueryBuilder();
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
    public function filters()
    {
        $queryParams = $this->getController()->params()->fromQuery();

        $this->searchService->init();
        $this->searchService
            ->initFilters()
            ->processQueryParams($queryParams);

        return $this->searchService->getFilters();
    }
}