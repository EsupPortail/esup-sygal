<?php

namespace Api;

use Laminas\ApiTools\Admin as ApiToolsAdmin;
use Laminas\ApiTools\Doctrine\Admin as ApiToolsDoctrineAdmin;
use Laminas\ApiTools\Documentation as ApiToolsDocumentation;
use Application\Provider\Privilege\AdministrationPrivileges;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'router' => [
        'routes' => [

        ],
    ],
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    // Ajout d'ACL pour accéder à Apigility
                    'controller' => [
                        // Apigility
                        ApiToolsAdmin\Controller\App::class,
                        ApiToolsAdmin\Controller\Authentication::class,
                        ApiToolsAdmin\Controller\Authorization::class,
                        ApiToolsAdmin\Controller\CacheEnabled::class,
                        ApiToolsAdmin\Controller\Config::class,
                        ApiToolsAdmin\Controller\FsPermissions::class,
                        ApiToolsAdmin\Controller\HttpBasicAuthentication::class,
                        ApiToolsAdmin\Controller\HttpDigestAuthentication::class,
                        ApiToolsAdmin\Controller\ModuleConfig::class,
                        ApiToolsAdmin\Controller\ModuleCreation::class,
                        ApiToolsAdmin\Controller\OAuth2Authentication::class,
                        ApiToolsAdmin\Controller\Package::class,
                        ApiToolsAdmin\Controller\Source::class,
                        ApiToolsAdmin\Controller\Versioning::class,

                        // Apigility Doctrine
                        ApiToolsDoctrineAdmin\Controller\DoctrineAutodiscovery::class,
                        ApiToolsDoctrineAdmin\Controller\DoctrineRestService::class,
                        ApiToolsDoctrineAdmin\Controller\DoctrineRpcService::class,
                        ApiToolsDoctrineAdmin\Controller\DoctrineMetadataService::class,
                    ],
//                    'privileges' => [
//                        AdministrationPrivileges::API_ADMINISTRATION,
//                    ],
                    'roles' => [],
                ],
                [
                    'controller' => [
                        // Api Documentation
                        ApiToolsDocumentation\Controller::class,
                    ],
//                    'privileges' => [
//                        AdministrationPrivileges::API_DOCUMENTATION,
//                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [

        ],
    ],
    'controllers' => [
        'factories' => [
        ],
    ],
];