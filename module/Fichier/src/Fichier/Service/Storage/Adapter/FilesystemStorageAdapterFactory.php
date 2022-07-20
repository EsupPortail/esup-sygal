<?php

namespace Fichier\Service\Storage\Adapter;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class FilesystemStorageAdapterFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function __invoke(ContainerInterface $container): FilesystemStorageAdapter
    {
        $config = $this->getStorageAdpaterConfig($container);

        $adapter = new FilesystemStorageAdapter();
        $adapter->setConfig($config);

        return $adapter;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getStorageAdpaterConfig(ContainerInterface $container): array
    {
        /** @var array $config */
        $config = $container->get('config');

        $array = $config['fichier']['storage']['adapters'][$key = FilesystemStorageAdapter::class] ?? null;
        Assert::notNull($array, "Clé de config introuvable : fichier > storage > adapters > $key");

        Assert::keyNotExists(
            $config['fichier'],
            'root_dir_path',
            "La clé de config 'fichier > root_dir_path' est obsolète, " .
            "elle est remplacée par la clé 'fichier > storage > adapters > $key > root_path', " .
            "vous devez corriger votre conifg."
        );

        return $array;
    }
}