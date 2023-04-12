<?php

namespace Formation\Controller\Recherche;

use Formation\Service\Inscription\Search\InscriptionSearchService;
use Psr\Container\ContainerInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class InscriptionRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionRechercheController
    {
        /**
         * @var \Formation\Service\Inscription\Search\InscriptionSearchService $searchService
         * @var ParametreService $parametreService
         */
        $searchService = $container->get(InscriptionSearchService::class);
        $parametreService = $container->get(ParametreService::class);

        $controller = new InscriptionRechercheController();
        $controller->setSearchService($searchService);
        $controller->setParametreService($parametreService);

        return $controller;
    }
}