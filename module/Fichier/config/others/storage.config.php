<?php

namespace Fichier;

use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\Fichier\FichierStorageServiceFactory;
use Fichier\Service\Storage\Adapter\FilesystemStorageAdapter;
use Fichier\Service\Storage\Adapter\S3StorageAdapter;
use Fichier\Service\Storage\StorageAdapterManager;
use Fichier\Service\Storage\StorageAdapterManagerFactory;

return [
    'fichier' => [
        'storage' => [
            'adapters' => [
                FilesystemStorageAdapter::class => [
                    'root_path' => '/app/upload',
                ],
                S3StorageAdapter::class => [
                    'client' => [
                        'end_point' => '',
                        'access_key' >= '',
                        'secret_key' => '',
                    ],
                    'root_path' => 'uniqueprefix',
                ],
            ],
            'adapter' => FilesystemStorageAdapter::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            FichierStorageService::class => FichierStorageServiceFactory::class,
            StorageAdapterManager::class => StorageAdapterManagerFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [

        ],
    ],
    'controller_plugins' => [
        'invokables' => [

        ],
    ],
];
