<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Session\Search\SessionSearchService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class SessionRechercheControllerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SessionRechercheController
    {
        /**
         * @var \Formation\Service\Session\Search\SessionSearchService $searchService
         * @var ParametreService $parametreService
         */
        $searchService = $container->get(SessionSearchService::class);
        $parametreService = $container->get(ParametreService::class);

        $controller = new SessionRechercheController();
        $controller->setSearchService($searchService);
        $controller->setParametreService($parametreService);

        return $controller;
    }
}