<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
use DomainException;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\View\Model\ViewModel;

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
     * @return Response|ZendPaginator
     * @see SearchControllerPlugin::searchIfRequested()
     */
    public function searchIfRequested()
    {
        return $this->getSearchPluginController()->searchIfRequested();
    }

    /**
     * @return Response|ZendPaginator
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

        return $this->getPluginManager()->get('searchControllerPlugin');
    }
}