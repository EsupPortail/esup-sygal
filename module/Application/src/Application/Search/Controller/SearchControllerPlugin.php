<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use DomainException;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Paginator\Adapter\ArrayAdapter;

/**
 * Class SearchControllerPlugin
 */
class SearchControllerPlugin extends AbstractPlugin
{
    /**
     * - Si les 'query params' de la requête courante ne contiennent pas les éventuels filtres et tris par défaut
     * obligatoires, cette méthode retourne une {@see Response} permettant de faire la redirection qui va bien.
     * - Sinon, elle retourne un {@see SearchResultPaginator} contenant le résultat paginé de la recherche.
     *
     * NB : Contrairement à {@see self::searchIfRequested()}, ici le paginator retourné contient systématiquement
     * un résultat de recherche (cf. {@see SearchResultPaginator::containsRealSearchResult()}).
     *
     * @return Response|SearchResultPaginator
     */
    public function search()
    {
        $queryParams = array_filter($this->getController()->params()->fromQuery());

        $searchService = $this->getSearchService();
        $searchService->init();

        // Mise à jour des 'query params' avec les filtres et tris par défaut éventuels, puis redirection si besoin.
        $filtersUpdated = $searchService->updateQueryParamsWithDefaultFilters($queryParams);
        $sortersUpdated = $searchService->updateQueryParamsWithDefaultSorters($queryParams);
        if ($filtersUpdated || $sortersUpdated) {
            return $this->getController()->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $searchService->initFiltersWithUnpopulatedOptions();
        $searchService->processQueryParams($queryParams);

        /** Configuration du paginator **/
        $qb = $searchService->getQueryBuilder();
        $maxi = $this->getController()->params()->fromQuery('maxi', 50);
        $page = $this->getController()->params()->fromQuery('page', 1);
        $paginator = new SearchResultPaginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setContainsRealSearchResult(true)
            ->setPageRange(30)
            ->setItemCountPerPage((int)$maxi)
            ->setCurrentPageNumber((int)$page);

        return $paginator;
    }

    /**
     * - Si les 'query params' de la requête courante contiennent la clé 'search', cette méthode se comporte
     * comme {@see self::search()}.
     * - Sinon, elle retourne un {@see LaminasPaginator} vide.
     *
     * Cela est utile pour ne lancer une recherche que lorsque l'utilisateur a cliqué sur le bouton "Rechercher"
     * du formulaire de recherche.
     *
     * @return Response|SearchResultPaginator
     */
    public function searchIfRequested()
    {
        $queryParams = array_filter($this->getController()->params()->fromQuery());

        if (! array_key_exists('search', $queryParams)) {
            $paginator = new SearchResultPaginator(new ArrayAdapter([]));
            $paginator->setContainsRealSearchResult(false);

            return $paginator;
        }

        return $this->search();
    }

    /**
     * @return SearchFilter[]
     */
    public function filters(): array
    {
        $queryParams = array_filter($this->getController()->params()->fromQuery());

        $searchService = $this->getSearchService();
        $searchService->init();
        $searchService->initFilters();

        // Application des filtres par défaut
        $searchService->updateQueryParamsWithDefaultFilters($queryParams);
        $searchService->processQueryParams($queryParams);

        return $searchService->getFilters();
    }

    /**
     * @return AbstractController
     */
    public function getController(): AbstractController
    {
        /** @var AbstractController $controller */
        $controller = parent::getController();
        if (! $controller instanceof SearchControllerInterface) {
            throw new DomainException('Ce plugin nécessite que le contrôleur implémente ' . SearchControllerInterface::class);
        }

        return $controller;
    }

    /**
     * @return SearchServiceInterface
     */
    protected function getSearchService(): SearchServiceInterface
    {
        /** @var SearchControllerInterface $controller */
        $controller = parent::getController();

        return $controller->getSearchService();
    }

}