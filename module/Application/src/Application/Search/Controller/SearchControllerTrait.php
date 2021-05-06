<?php

namespace Application\Search\Controller;

use Application\Search\Filter\SearchFilter;
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
        /** @var AbstractActionController $that */
        $that = $this;
        /** @var SearchControllerPlugin $searchControllerPlugin */
        $searchControllerPlugin = $that->getPluginManager()->get('searchControllerPlugin');

        return $searchControllerPlugin;
    }
}