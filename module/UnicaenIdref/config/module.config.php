<?php

namespace UnicaenIdref;

use UnicaenAuth\Guard\PrivilegeController;
use UnicaenIdref\Controller\IndexController;
use UnicaenIdref\Controller\IndexControllerFactory;
use UnicaenIdref\View\Helper\IdrefPopupTriggerViewHelper;
use UnicaenIdref\View\Helper\IdrefPopupTriggerViewHelperFactory;

return [
    'id_ref' => [

    ],
    'router' => [
        'routes' => [
            'unicaen-id-ref' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/unicaen-id-ref',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    /**
                     * @see IndexController::indexAction()
                     */
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'roles' => [],
//                    'privileges' => \Application\Provider\Privilege\ThesePrivileges::THESE_RECHERCHE,
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
        ]
    ],
    'view_helpers' => [
        'factories' => [
            IdrefPopupTriggerViewHelper::class => IdrefPopupTriggerViewHelperFactory::class,
        ],
        'aliases' => [
            'idrefPopupTrigger' => IdrefPopupTriggerViewHelper::class,
        ],
        'shared' => [
            IdrefPopupTriggerViewHelper::class => false,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'public_files' => [
        'head_scripts' => [
            '099_unicaen-idref_1' => '/unicaen/idref/js/formulaire.js',
            '099_unicaen-idref_2' => '/unicaen/idref/js/subModal.js',
            '099_unicaen-idref_3' => '/unicaen/idref/js/trigger.js',
        ],
        'stylesheets' => [
            '099_unicaen-idref_1' => '/unicaen/idref/css/subModal.css',
            '099_unicaen-idref_2' => '/unicaen/idref/css/trigger.css',
        ],
    ],
];