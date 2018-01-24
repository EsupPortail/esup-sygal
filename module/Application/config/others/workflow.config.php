<?php

use Application\Acl\WfEtapeResource;
use Application\Assertion\WorkflowAssertion;
use Application\Controller\Plugin\UrlWorkflow;
use Application\Controller\WorkflowController;
use Application\ORM\Query\Functions\Atteignable;
use Application\Service\Workflow\WorkflowService;
use Application\View\Helper\Workflow\RoadmapHelper;
use Application\View\Helper\Workflow\WorkflowHelper;
use Application\View\Helper\Workflow\WorkflowStepHelper;
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
                        'assertion' => 'WorkflowAssertion',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Workflow',
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
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Workflow',
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
        'invokables' => [
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
