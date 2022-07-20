<?php

namespace Fichier\Controller\Factory;

use Fichier\Controller\ConsoleController;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Storage\StorageAdapterManager;
use Psr\Container\ContainerInterface;

class ConsoleControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConsoleController
    {
        $controller = new ConsoleController();

        /** @var \Fichier\Service\Fichier\FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);
        $controller->setFichierService($fichierService);

        /** @var \Fichier\Service\Storage\StorageAdapterManager $storageAdapterManager */
        $storageAdapterManager = $container->get(StorageAdapterManager::class);
        $controller->setStorageAdapterManager($storageAdapterManager);

        return $controller;
    }
}