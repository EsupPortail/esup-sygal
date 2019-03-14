<?php

namespace Soutenance;

use Soutenance\Controller\ConfigurationController;
use Soutenance\Controller\Qualite\QualiteControllerFactory;
use Soutenance\Controller\Qualite\QualiteController;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteEdition\QualiteEditionFormFactory;
use Soutenance\Form\QualiteEdition\QualiteEditiontHydrator;
use Soutenance\Provider\Privilege\QualitePrivileges;
use Soutenance\Service\Qualite\QualiteService;
use Soutenance\Service\Qualite\QualiteServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER,
                ],
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'editer',
                        'effacer',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
                ],
                [
                    'controller' => ConfigurationController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
                ],

            ],
        ],
    ],

    'router' => [
        'routes' => [
            'qualite' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/qualite',
                    'defaults' => [
                        'controller' => QualiteController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'editer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/editer[/:qualite]',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action'     => 'editer',
                            ],
                        ],
                    ],
                    'effacer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/effacer/:qualite',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action'     => 'effacer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            QualiteService::class => QualiteServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            QualiteController::class => QualiteControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            QualiteEditionForm::class => QualiteEditionFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            QualiteEditiontHydrator::class => QualiteEditiontHydrator::class,
        ],
    ],
);
