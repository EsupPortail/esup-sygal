<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Formation\Search\FormationSearchService;
use Psr\Container\ContainerInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class FormationRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationRechercheController
    {
        /**
         * @var \Formation\Service\Formation\Search\FormationSearchService $searchService
         * @var ParametreService $parametreService
         */
        $searchService = $container->get(FormationSearchService::class);
        $parametreService = $container->get(ParametreService::class);

        $controller = new FormationRechercheController();
        $controller->setSearchService($searchService);
        $controller->setParametreService($parametreService);

        return $controller;
    }
}