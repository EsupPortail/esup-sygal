<?php

namespace Application\Search\Filter;

interface SearchFilterPluginManagerAwareInterface
{
    public function setSearchFilterPluginManager(SearchFilterPluginManager $searchFilterPluginManager);
}