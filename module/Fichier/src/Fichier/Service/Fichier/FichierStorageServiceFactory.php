<?php

namespace Fichier\Service\Fichier;

use Fichier\Service\Storage\Adapter\StorageAdapterInterface;
use Fichier\Service\Storage\StorageAdapterManager;
use Interop\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class FichierStorageServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return FichierStorageService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierStorageService
    {
        $service = new FichierStorageService();

        $storageAdapter = $this->getStorageAdpater($container);
        $service->setStorageAdapter($storageAdapter);

        return $service;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getStorageAdpater(\Psr\Container\ContainerInterface $container): StorageAdapterInterface
    {
        /** @var array $config */
        $config = $container->get('Config');

        $storageAdapterServiceName = $config['fichier']['storage']['adapter'] ?? null;
        Assert::notNull($storageAdapterServiceName, "Clé de config introuvable : 'fichier > storage > adapter'");

        /** @var \Fichier\Service\Storage\StorageAdapterManager $storageAdapterManager */
        $storageAdapterManager = $container->get(StorageAdapterManager::class);

        return $storageAdapterManager->get($storageAdapterServiceName);
    }
}
