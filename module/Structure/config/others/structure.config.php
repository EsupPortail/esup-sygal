<?php

use Structure\Assertion\Structure\StructureAssertion;
use Structure\Assertion\Structure\StructureAssertionFactory;
use Structure\Controller\Factory\StructureControllerFactory;
use Structure\Controller\StructureController;
use Structure\Provider\Privilege\StructurePrivileges;
use Structure\Service\Structure\StructureService;
use Structure\Service\Structure\StructureServiceFactory;
use Structure\Service\StructureDocument\StructureDocumentService;
use Structure\Service\StructureDocument\StructureDocumentServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;
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
                        'assertion'  => StructureAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => StructureController::class,
                    'action'     => [
                        'voir',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                    ],
                ],
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
                    'voir' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/voir/:structure',
                            'defaults'    => [
                                /** @see StructureController::voirAction() */
                                'action' => 'voir',
                            ],
                        ],
                    ],
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
                                /** @see StructureController::genererRolesDefautsAction() */
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
            StructureAssertion::class => StructureAssertionFactory::class,
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
];
