<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Formation\Search\FormationSearchService;
use Psr\Container\ContainerInterface;

class FormationRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationRechercheController
    {
        /** @var \Formation\Service\Formation\Search\FormationSearchService $searchService */
        $searchService = $container->get(FormationSearchService::class);

        $controller = new FormationRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}