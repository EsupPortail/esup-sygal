<?php

namespace Soutenance;

use Soutenance\Assertion\JustificatifAssertion;
use Soutenance\Assertion\JustificatifAssertionFactory;
use Soutenance\Controller\JustificatifController;
use Soutenance\Controller\JustificatifControllerFactory;
use Soutenance\Form\Justificatif\JusticatifHydrator;
use Soutenance\Form\Justificatif\JustificatifForm;
use Soutenance\Form\Justificatif\JustificatifFormFactory;
use Soutenance\Form\Justificatif\JustificatifHydratorFactory;
use Soutenance\Provider\Privilege\JustificatifPrivileges;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Justificatif\JustificatifServiceFactory;
use Soutenance\View\Helper\JustificatifViewHelper;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            JustificatifPrivileges::JUSTIFICATIF_AJOUTER,
                            JustificatifPrivileges::JUSTIFICATIF_RETIRER,
                        ],
                        'resources' => ['These'],
                        'assertion' => JustificatifAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => JustificatifController::class,
                    'action' => [
                        'ajouter',
                        'ajouter-justificatif',
                    ],
                    'privileges' => JustificatifPrivileges::JUSTIFICATIF_AJOUTER,
                ],
                [
                    'controller' => JustificatifController::class,
                    'action' => [
                        'retirer',
                    ],
                    'privileges' => JustificatifPrivileges::JUSTIFICATIF_RETIRER,
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'child_routes' => [
                    'justificatif' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/justificatif',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'ajouter' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ajouter/:proposition/:nature/:membre',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        'action' => 'ajouter',
                                    ],
                                ],
                            ],
                            'retirer' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/retirer/:justificatif',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        'action' => 'retirer',
                                    ],
                                ],
                            ],
                            'ajouter-justificatif' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ajouter-justificatif/:these',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        'action' => 'ajouter-justificatif',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            JustificatifAssertion::class => JustificatifAssertionFactory::class,
            JustificatifService::class => JustificatifServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            JustificatifController::class => JustificatifControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            JustificatifForm::class => JustificatifFormFactory::class,
        ],
    ],

    'hydrators' => [
        'factories' => [
            JusticatifHydrator::class => JustificatifHydratorFactory::class,
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'justificatif' => JustificatifViewHelper::class,
        ],
    ],
];