<?php

namespace Formation\Controller\Recherche;

/**
 * @property \Formation\Service\Inscription\Search\InscriptionSearchService $searchService
 */
class InscriptionRechercheController extends AbstractRechercheController
{
    protected string $routeName = 'formation/inscription';
    protected string $indexActionTemplate = 'formation/inscription/recherche/index';
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title = "Inscriptions";
}