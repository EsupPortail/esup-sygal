<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Session\Search\SessionSearchService;
use Psr\Container\ContainerInterface;

class SessionRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SessionRechercheController
    {
        /** @var \Formation\Service\Session\Search\SessionSearchService $searchService */
        $searchService = $container->get(SessionSearchService::class);

        $controller = new SessionRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}