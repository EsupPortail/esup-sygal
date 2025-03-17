<?php

namespace Application\Search\Filter;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

class SearchFilterPluginManager extends AbstractPluginManager
{
    protected $instanceOf = SearchFilter::class;

    protected $factories = [
        CheckboxSearchFilter::class => InvokableFactory::class,
        StrReducedTextSearchFilter::class => InvokableFactory::class,
        TextCriteriaSearchFilter::class => InvokableFactory::class,
        TextSearchFilter::class => InvokableFactory::class,
        WhereSearchFilter::class => InvokableFactory::class,
//        EstHistoriseSearchFilter::class => InvokableFactory::class,
    ];
}