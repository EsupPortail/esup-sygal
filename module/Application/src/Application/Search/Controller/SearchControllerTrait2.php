<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use DomainException;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;

trait SearchControllerTrait2
{
    use SearchServiceAwareTrait;

    protected int $paginatorItemCountPerPage = 50;

    /**
     * @see \Laminas\Paginator\Paginator::setItemCountPerPage()
     */
    public function setPaginatorItemCountPerPage(int $paginatorItemCountPerPage): void
    {
        $this->paginatorItemCountPerPage = $paginatorItemCountPerPage;
    }

    public function filtersAction(): ViewModel
    {
        $filters = $this->filters();

        return new ViewModel([
            'filters' => $filters,
        ]);
    }

    /**
     * Méthode à implémenter pour appliquer éventuellement des filtrages/remaniements sur les filtres,
     * exemples : restreindre la liste des ED sélectionnables en fonction du rôle de l'utilisateur courant ;
     * rendre un filtre invisible, etc.
     */
    abstract protected function restrictFilters(): void;
    
    /**
     * - Si les 'query params' de la requête courante contiennent la clé 'search', cette méthode se comporte
     * comme {@see self::search()}.
     * - Sinon, elle retourne un {@see LaminasPaginator} vide.
     *
     * Cela est utile pour ne lancer une recherche que lorsque l'utilisateur a cliqué sur le bouton "Rechercher"
     * du formulaire de recherche.
     *
     * @param callable|null $queryBuilderModifierCallback Fonction de rappel éventuelle permettant d'agir sur
     * le query builder généré par le {@see \Application\Search\SearchService}. Cette fonction doit accepter en argument
     * un {@see \Doctrine\ORM\QueryBuilder}.
     */
    public function searchIfRequested(?callable $queryBuilderModifierCallback = null): Response|SearchResultPaginator
    {
        $queryParams = array_filter($this->getController()->params()->fromQuery());

        if (! array_key_exists('search', $queryParams)) {
            $paginator = new SearchResultPaginator(new ArrayAdapter([]));
            $paginator->setContainsRealSearchResult(false);

            return $paginator;
        }

        return $this->search($queryBuilderModifierCallback);
    }

    /**
     * - Si les 'query params' de la requête courante ne contiennent pas les éventuels filtres et tris par défaut
     * obligatoires, cette méthode retourne une {@see Response} permettant de faire la redirection qui va bien.
     * - Sinon, elle retourne un {@see SearchResultPaginator} contenant le résultat paginé de la recherche.
     *
     * NB : Contrairement à {@see self::searchIfRequested()}, ici le paginator retourné contient systématiquement
     * un résultat de recherche (cf. {@see SearchResultPaginator::containsRealSearchResult()}).
     *
     * @param callable|null $queryBuilderModifierCallback Fonction de rappel éventuelle permettant d'agir sur
     * le query builder généré par le {@see \Application\Search\SearchService}. Cette fonction doit accepter en argument
     * un {@see \Doctrine\ORM\QueryBuilder}.
     */
    public function search(?callable $queryBuilderModifierCallback = null): Response|SearchResultPaginator
    {
        $queryParams = array_filter(
            $this->getController()->params()->fromQuery(),
            fn($value) => $value || $value === '0'
        );

        $searchService = $this->getSearchService();
        $searchService->init();

        $this->restrictFilters();

        // Mise à jour des 'query params' avec les filtres et tris par défaut éventuels, puis redirection si besoin.
        $filtersUpdated = $searchService->updateQueryParamsWithDefaultFilters($queryParams);
        $sortersUpdated = $searchService->updateQueryParamsWithDefaultSorters($queryParams);
        if ($filtersUpdated || $sortersUpdated) {
            return $this->getController()->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $searchService->initFiltersWithUnpopulatedOptions();
        $searchService->processQueryParams($queryParams);

        $qb = $searchService->getQueryBuilder();
        if ($queryBuilderModifierCallback !== null) {
            $queryBuilderModifierCallback($qb);
        }

        /** Configuration du paginator **/
        $maxi = $this->getController()->params()->fromQuery('maxi', $this->paginatorItemCountPerPage);
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
     * @return SearchFilter[]
     */
    public function filters(): array
    {
        $queryParams = array_filter($this->getController()->params()->fromQuery());

        $searchService = $this->getSearchService();
        $searchService->init();
        $searchService->initFilters();

        $this->restrictFilters();

        // Application des filtres par défaut
        $searchService->updateQueryParamsWithDefaultFilters($queryParams);
        $searchService->processQueryParams($queryParams);

        return $searchService->getFilters();
    }

    public function getController(): AbstractController|SearchControllerInterface
    {
        if (! $this instanceof AbstractController) {
            throw new DomainException("Ce trait n'est utilisable que sur un contrôleur de type " . AbstractController::class);
        }
        if (! $this instanceof SearchControllerInterface) {
            throw new DomainException("Ce trait n'est utilisable que sur un contrôleur implémentant " . SearchControllerInterface::class);
        }

        /** @var AbstractController|SearchControllerInterface $this */
        return $this;
    }
}