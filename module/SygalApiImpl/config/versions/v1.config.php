<?php
/**
 * Surcharge de la config homonyme de "unicaen/sygal-api".
 */

return [
    'api-tools-rest' => [
        //
        // Substitution des resources d'unicaen/sygal-api par les nôtres.
        //
        'SygalApi\\V1\\Rest\\Ping\\Controller' => [
            'entity_http_methods' => [
                //'GET' => '', // astuce pour "désactiver" la méthode
            ],
        ],
        'SygalApi\\V1\\Rest\\InscriptionAdministrative\\Controller' => [
            'listener' => \SygalApiImpl\V1\Rest\InscriptionAdministrative\InscriptionAdministrativeResource::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            \SygalApiImpl\V1\Rest\InscriptionAdministrative\InscriptionAdministrativeResource::class => \SygalApiImpl\V1\Rest\InscriptionAdministrative\InscriptionAdministrativeResourceFactory::class,
            \SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade\ImportFacade::class => \SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade\ImportFacadeFactory::class
        ],
    ],
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => 'SygalApi\\V1\\Rest\\Ping\\Controller',
//                    'action'     => [
//                        'index',
//                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'SygalApi\\V1\\Rest\\Diplomation\\Controller',
//                    'action'     => [
//                        'index',
//                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'SygalApi\\V1\\Rest\\InscriptionAdministrative\\Controller',
//                    'action'     => [
//                        'index',
//                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
];
