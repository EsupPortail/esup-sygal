<?php

namespace Fichier\Controller\Factory;

use Fichier\Controller\FichierController;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Psr\Container\ContainerInterface;

class FichierControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return FichierController
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierController
    {
        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);

        /** @var \Fichier\Service\Fichier\FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        $service = new FichierController();
        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);

        return $service;
    }
}