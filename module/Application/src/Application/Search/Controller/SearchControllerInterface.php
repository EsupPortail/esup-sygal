<?php

namespace Application\Search\Controller;

use Application\Search\SearchServiceInterface;
use Zend\View\Model\ViewModel;

interface SearchControllerInterface
{
    /**
     * @return SearchServiceInterface
     */
    public function getSearchService();

    /**
     * @return ViewModel
     */
    public function filtersAction();
}