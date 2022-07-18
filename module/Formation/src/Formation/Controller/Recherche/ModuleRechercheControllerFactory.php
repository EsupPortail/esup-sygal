<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Module\Search\ModuleSearchService;
use Psr\Container\ContainerInterface;

class ModuleRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ModuleRechercheController
    {
        /** @var \Formation\Service\Module\Search\ModuleSearchService $searchService */
        $searchService = $container->get(ModuleSearchService::class);

        $controller = new ModuleRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}