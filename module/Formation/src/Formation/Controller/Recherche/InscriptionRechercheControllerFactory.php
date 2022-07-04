<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Inscription\Search\InscriptionSearchService;
use Psr\Container\ContainerInterface;

class InscriptionRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionRechercheController
    {
        /** @var \Formation\Service\Inscription\Search\InscriptionSearchService $searchService */
        $searchService = $container->get(InscriptionSearchService::class);

        $controller = new InscriptionRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}