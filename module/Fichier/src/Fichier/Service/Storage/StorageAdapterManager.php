<?php

namespace Fichier\Service\Storage;

use Fichier\Service\Storage\Adapter\FilesystemStorageAdapter;
use Fichier\Service\Storage\Adapter\FilesystemStorageAdapterFactory;
use Fichier\Service\Storage\Adapter\S3StorageAdapter;
use Fichier\Service\Storage\Adapter\S3StorageAdapterFactory;
use Fichier\Service\Storage\Adapter\StorageAdapterInterface;
use Laminas\ServiceManager\AbstractPluginManager;

class StorageAdapterManager extends AbstractPluginManager
{
    protected $instanceOf = StorageAdapterInterface::class;

    protected $factories = [
        FilesystemStorageAdapter::class => FilesystemStorageAdapterFactory::class,
        S3StorageAdapter::class => S3StorageAdapterFactory::class,
    ];

    protected $aliases = [
        'FilesystemStorageAdapter' => FilesystemStorageAdapter::class,
        'S3StorageAdapter' => S3StorageAdapter::class,
    ];
}