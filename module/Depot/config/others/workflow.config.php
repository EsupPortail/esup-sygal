<?php

use Depot\Acl\WfEtapeResource;
use Depot\Assertion\WorkflowAssertion;
use Depot\Controller\Plugin\UrlWorkflow;
use Depot\Controller\WorkflowController;
use Depot\ORM\Query\Functions\Atteignable;
use Depot\Service\Workflow\WorkflowService;
use Depot\View\Helper\Workflow\RoadmapHelper;
use Depot\View\Helper\Workflow\WorkflowHelper;
use Depot\View\Helper\Workflow\WorkflowStepHelper;
use Laminas\ServiceManager\Factory\InvokableFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'string_functions' => [
                    'atteignable' => Atteignable::class,
                ],
            ],
        ],
    ],

    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                WfEtapeResource::RESOURCE_ID => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'resources' => WfEtapeResource::RESOURCE_ID,
                        'assertion' => WorkflowAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => WorkflowController::class,
                    'action'     => [
                        'next-step-box',
                    ],
                    'roles' => 'user',
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'workflow' => [
                'type'          => 'Segment',
                'options'       => [
                    'route' => '/workflow',
                    'defaults'      => [
                        'controller' => WorkflowController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'next-step-box' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/next-step-box/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'next-step-box',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'WorkflowService' => WorkflowService::class,
            'WorkflowAssertion' => WorkflowAssertion::class,
        ],
        'factories' => [
        ],
    ],
    'controllers' => [
        'factories' => [
            WorkflowController::class => InvokableFactory::class,
        ],
        'aliases' => [
            'Application\Controller\Workflow' => WorkflowController::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlWorkflow' => UrlWorkflow::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'wf'      => WorkflowHelper::class,
            'wfs'     => WorkflowStepHelper::class,
            'roadmap' => RoadmapHelper::class,
        ],
    ],
];
