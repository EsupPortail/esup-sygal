<?php

namespace Soutenance;

use Soutenance\Assertion\AvisSoutenanceAssertion;
use Soutenance\Assertion\AvisSoutenanceAssertionFactory;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\AvisControllerFactory;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormFactory;
use Soutenance\Form\Avis\AvisHydrator;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Avis\AvisServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return array(
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
                            AvisSoutenancePrivileges::AVIS_VISUALISER,
                            AvisSoutenancePrivileges::AVIS_MODIFIER,
                            AvisSoutenancePrivileges::AVIS_ANNULER,
                        ],
                        'resources' => ['Acteur'],
                        'assertion' => AvisSoutenanceAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AvisController::class,
                    'action' => [
                        'index',
                        'afficher',
                    ],
                    'privileges' => AvisSoutenancePrivileges::AVIS_VISUALISER,
                ],
                [
                    'controller' => AvisController::class,
                    'action' => [
                        'annuler',
                    ],
                    'privileges' => AvisSoutenancePrivileges::AVIS_ANNULER,
                ],
                [
                    'controller' => AvisController::class,
                    'action' => [
                        'telecharger',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'child_routes' => [
                    'avis-soutenance' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/avis-soutenance/:rapporteur',
                            'defaults' => [
                                'controller' => AvisController::class,
                                'action' => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'afficher' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/afficher',
                                    'defaults' => [
                                        'controller' => AvisController::class,
                                        'action' => 'afficher',
                                    ],
                                ],
                            ],
                            'annuler' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/annuler',
                                    'defaults' => [
                                        'controller' => AvisController::class,
                                        'action' => 'annuler',
                                    ],
                                ],
                            ],
                            'telecharger' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/telecharger',
                                    'defaults' => [
                                        'controller' => AvisController::class,
                                        'action' => 'telecharger',
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
            AvisService::class => AvisServiceFactory::class,
            AvisSoutenanceAssertion::class => AvisSoutenanceAssertionFactory::class,

        ],
    ],
    'controllers' => [
        'factories' => [
            AvisController::class => AvisControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            AvisForm::class => AvisFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            AvisHydrator::class => AvisHydrator::class,
        ],
    ],
);
