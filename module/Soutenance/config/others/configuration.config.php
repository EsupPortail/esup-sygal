<?php

namespace Soutenance;

use Soutenance\Controller\ConfigurationController;
use Soutenance\Controller\ConfigurationControllerFactory;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Form\Configuration\ConfigurationFormFactory;
use Soutenance\Provider\Privilege\QualitePrivileges;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Parametre\ParametreServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;

return array(
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ConfigurationController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
                ],

            ],
        ],
    ],

    'router' => [
        'routes' => [
            'configuration' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/configuration',
                    'defaults' => [
                        'controller' => ConfigurationController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'qualite' => [
                                'label' => 'Qualités des membres',
                                'route' => 'qualite',
                                'order' => 1000,
                                'resource' => QualitePrivileges::getResourceId(QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],


    'service_manager' => [
        'factories' => [
            ParametreService::class => ParametreServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ConfigurationController::class => ConfigurationControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            ConfigurationForm::class => ConfigurationFormFactory::class,
        ],
    ],

);
