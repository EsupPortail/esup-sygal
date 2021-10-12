<?php

use Application\Controller\Factory\StructureControllerFactory;
use Application\Controller\StructureController;
use Application\Entity\Db\StructureDocument;
use Application\Provider\Privilege\EtablissementPrivileges;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\Structure\StructureService;
use Application\Service\Structure\StructureServiceFactory;
use Application\Service\StructureDocument\StructureDocumentService;
use Application\Service\StructureDocument\StructureDocumentServiceFactory;
use Application\View\Helper\StructureSubstitHelper;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Laminas\Router\Http\Segment;

return [

    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'structure' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                            StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                            StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                            StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
                            StructurePrivileges::STRUCTURE_CREATION_ETAB,
                            StructurePrivileges::STRUCTURE_CREATION_ED,
                            StructurePrivileges::STRUCTURE_CREATION_UR,
                        ],
                        'resources'  => ['structure'],
                        'assertion'  => 'Assertion\\Structure',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => StructureController::class,
                    'action'     => [
                        'individu-role',
                        'generer-roles-defauts',
                    ],
                    'privileges' => StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                ],
                [
                    'controller' => StructureController::class,
                    'action'     => [
                        'televerser-document',
                        'supprimer-document',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'structure' => [
                'type'          => Segment::class,
                'may_terminate' => false,
                'options'       => [
                    'route'    => '/structure',
                    'defaults' => [
                        'controller'    => StructureController::class,
                    ],
                ],
                'child_routes'  => [
                    'individu-role' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/individu-role/:structure[/:type]',
                            'defaults'    => [
                                'action' => 'individu-role',
                            ],
                        ],
                    ],
                    'generer-roles-defauts' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/generer-roles-defauts/:id/:type',
                            'defaults'    => [
                                'action' => 'generer-roles-defauts',
                            ],
                        ],
                    ],
                    'televerser-document' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/televerser-document/:structure',
                            'defaults'    => [
                                'action' => 'televerser-document',
                            ],
                        ],
                    ],
                    'supprimer-document' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer-document/:structure/:document',
                            'defaults'    => [
                                'action' => 'supprimer-document',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
        ],
    ],
    'service_manager' => [
        'factories' => [
            StructureService::class => StructureServiceFactory::class,
            StructureDocumentService::class => StructureDocumentServiceFactory::class,
        ],
        'aliases' => [
            'StructureService' => StructureService::class,
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            StructureController::class => StructureControllerFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => array(
            'structureSubstitHelper' => StructureSubstitHelper::class,
        ),
        'factories' => [],
    ],
];
