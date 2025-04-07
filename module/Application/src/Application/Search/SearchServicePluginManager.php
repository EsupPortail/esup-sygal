<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilterPluginManager;
use Application\Search\Filter\SearchFilterPluginManagerAwareInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Psr\Container\ContainerInterface;

class SearchServicePluginManager extends AbstractPluginManager
{
    protected $instanceOf = SearchService::class;

    protected SearchFilterPluginManager $searchFilterPluginManager;

    public function setSearchFilterPluginManager(SearchFilterPluginManager $searchFilterPluginManager): void
    {
        $this->searchFilterPluginManager = $searchFilterPluginManager;
    }

    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        parent::__construct($configInstanceOrParentLocator, $config);

        $this->addInitializer([$this, 'injectSearchFilterPluginManager']);
    }

    /**
     * Inject the search filter plugin manager
     *
     * @param  ContainerInterface|object $first
     * @param  ContainerInterface|object $second
     * @return void
     */
    public function injectSearchFilterPluginManager($first, $second): void
    {
        if ($first instanceof ContainerInterface) {
            $searchFilter = $second;
        } else {
            $searchFilter = $first;
        }
        if ($searchFilter instanceof SearchFilterPluginManagerAwareInterface) {
            $searchFilter->setSearchFilterPluginManager($this->searchFilterPluginManager);
        }
    }
}