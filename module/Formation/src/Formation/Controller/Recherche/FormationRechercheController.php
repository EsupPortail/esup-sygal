<?php

namespace Formation\Controller\Recherche;

/**
 * @property \Formation\Service\Formation\Search\FormationSearchService $searchService
 */
class FormationRechercheController extends AbstractRechercheController
{
    protected string $routeName = 'formation/formation';
    protected string $indexActionTemplate = 'formation/formation/recherche/index';
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title = "Formations";
}