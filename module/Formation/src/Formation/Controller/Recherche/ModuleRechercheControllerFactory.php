<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Module\Search\ModuleSearchService;
use Psr\Container\ContainerInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class ModuleRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ModuleRechercheController
    {
        /**
         * @var \Formation\Service\Module\Search\ModuleSearchService $searchService
         * @var ParametreService $parametreService
         */
        $searchService = $container->get(ModuleSearchService::class);
        $parametreService = $container->get(ParametreService::class);


        $controller = new ModuleRechercheController();
        $controller->setSearchService($searchService);
        $controller->setParametreService($parametreService);

        return $controller;
    }
}