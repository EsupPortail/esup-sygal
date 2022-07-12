<?php

namespace Formation\Controller\Recherche;

/**
 * @property \Formation\Service\Session\Search\SessionSearchService $searchService
 */
class SessionRechercheController extends AbstractRechercheController
{
    protected string $routeName = 'formation/session';
    protected string $indexActionTemplate = 'formation/session/recherche/index';
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title = "Sessions";
}