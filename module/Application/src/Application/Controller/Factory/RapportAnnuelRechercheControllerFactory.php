<?php

namespace Application\Controller\Factory;

use Application\Controller\RapportAnnuelRechercheController;
use Application\Service\Fichier\FichierService;
use Application\Service\RapportAnnuel\RapportAnnuelSearchService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class RapportAnnuelRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $container = $container->getServiceLocator();

        /**
         * @var RapportAnnuelSearchService $searchService
         * @var FichierService $fichierService
         */
        $searchService = $container->get(RapportAnnuelSearchService::class);
        $fichierService = $container->get(FichierService::class);

        $controller = new RapportAnnuelRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);

        return $controller;
    }
}