<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
use DomainException;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;

/**
 * Trait SearchControllerTrait
 */
trait SearchControllerTrait
{
    /**
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $filters = $this->filters();

        return new ViewModel([
            'filters' => $filters,
        ]);
    }

    /**
     * @param callable|null $queryBuilderModifierCallback Fonction de rappel éventuelle permettant d'agir sur
     * le query builder généré par le {@see \Application\Search\SearchService}. Cette fonction doit accepter en argument
     * un {@see \Doctrine\ORM\QueryBuilder}.
     *
     * @return Response|LaminasPaginator
     * @see SearchControllerPlugin::searchIfRequested()
     */
    public function searchIfRequested(?callable $queryBuilderModifierCallback = null)
    {
        return $this->getSearchPluginController()->searchIfRequested($queryBuilderModifierCallback);
    }

    /**
     * @return Response|LaminasPaginator
     * @see SearchControllerPlugin::search()
     */
    public function search()
    {
        return $this->getSearchPluginController()->search();
    }

    /**
     * @return SearchFilter[]
     */
    public function filters(): array
    {
        return $this->getSearchPluginController()->filters();
    }

    /**
     * @return SearchControllerPlugin
     */
    protected function getSearchPluginController(): SearchControllerPlugin
    {
        if (! $this instanceof AbstractActionController) {
            throw new DomainException("Ce trait n'est utilisable que sur un contrôleur de type " . AbstractActionController::class);
        }

        /** @var SearchControllerPlugin $plugin */
        $plugin = $this->getPluginManager()->get('searchControllerPlugin');

        return $plugin;
    }
}