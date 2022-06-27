<?php

namespace Fichier\Service\Storage\Adapter;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class S3StorageAdapterFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function __invoke(ContainerInterface $container): S3StorageAdapter
    {
        $adapterConfig = $this->getStorageAdpaterConfig($container);

        $adapter = new S3StorageAdapter();
        $adapter->setConfig($adapterConfig);

        return $adapter;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getStorageAdpaterConfig(ContainerInterface $container): array
    {
        /** @var array $config */
        $config = $container->get('Config');

        $array = $config['fichier']['storage']['adapters'][$key = S3StorageAdapter::class] ?? null;
        Assert::notNull($array, "Clé de config introuvable : 'fichier > storage > adapters > $key'");

        return $array;
    }
}