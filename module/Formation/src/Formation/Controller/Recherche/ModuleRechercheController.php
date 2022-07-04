<?php

namespace Formation\Controller\Recherche;

/**
 * @property \Formation\Service\Module\Search\ModuleSearchService $searchService
 */
class ModuleRechercheController extends AbstractRechercheController
{
    protected string $routeName = 'formation/module';
    protected string $indexActionTemplate = 'formation/module/recherche/index';
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title = "Modules de formation";
}