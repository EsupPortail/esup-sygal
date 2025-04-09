<?php

namespace Fichier\Controller\Factory;

use UnicaenSearch\SearchServicePluginManager;
use Fichier\Controller\FichierController;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\Fichier\Search\FichierSearchService;
use Psr\Container\ContainerInterface;

class FichierControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierController
    {
        $service = new FichierController();

        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);
        $service->setFichierService($fichierService);

        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);
        $service->setFichierStorageService($fileService);

        /** @var FichierSearchService $fileService */
        $fileService = $container->get(SearchServicePluginManager::class)->get(FichierSearchService::class);
        $service->setSearchService($fileService);
        
        return $service;
    }
}