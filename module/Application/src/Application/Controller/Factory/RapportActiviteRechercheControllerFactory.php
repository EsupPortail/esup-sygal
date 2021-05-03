<?php

namespace Application\Controller\Factory;

use Application\Controller\RapportActiviteRechercheController;
use Application\Service\Fichier\FichierService;
use Application\Service\Rapport\RapportSearchService;
use Interop\Container\ContainerInterface;

class RapportActiviteRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var RapportSearchService $searchService
         * @var FichierService $fichierService
         */
        $searchService = $container->get(RapportSearchService::class);
        $fichierService = $container->get(FichierService::class);

        $controller = new RapportActiviteRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);

        return $controller;
    }
}