<?php

namespace Soutenance;

use Soutenance\Controller\ConfigurationController;
use Soutenance\Controller\QualiteController;
use Soutenance\Controller\QualiteControllerFactory;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteEdition\QualiteEditionFormFactory;
use Soutenance\Form\QualiteEdition\QualiteEditiontHydrator;
use Soutenance\Form\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireForm;
use Soutenance\Form\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireFormFactory;
use Soutenance\Form\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireHydrator;
use Soutenance\Provider\Privilege\QualitePrivileges;
use Soutenance\Service\Qualite\QualiteService;
use Soutenance\Service\Qualite\QualiteServiceFactory;
use Soutenance\Service\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireService;
use Soutenance\Service\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return array(
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => QualiteController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER,
                ],
                [
                    'controller' => QualiteController::class,
                    'action' => [
                        'editer',
                        'effacer',
                        'ajouter-libelle-supplementaire',
                        'retirer-libelle-supplementaire'
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
                ],
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
            'qualite' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/qualite',
                    'defaults' => [
                        'controller' => QualiteController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'editer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/editer[/:qualite]',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action' => 'editer',
                            ],
                        ],
                    ],
                    'effacer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/effacer/:qualite',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action' => 'effacer',
                            ],
                        ],
                    ],
                    'ajouter-libelle-supplementaire' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/ajouter-libelle-supplementaire/:qualite',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action' => 'ajouter-libelle-supplementaire',
                            ],
                        ],
                    ],
                    'retirer-libelle-supplementaire' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/retirer-libelle-supplementaire/:libelle',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action' => 'retirer-libelle-supplementaire',
                            ],
                        ],
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
                            'configuration' => [
                                'label' => 'Paramétrage du module de soutenance',
                                'route' => 'configuration',
                                'order' => 2000,
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
            QualiteService::class => QualiteServiceFactory::class,
            QualiteLibelleSupplementaireService::class => QualiteLibelleSupplementaireServiceFactory::class,
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
            QualiteLibelleSupplementaireForm::class => QualiteLibelleSupplementaireFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            QualiteEditiontHydrator::class => QualiteEditiontHydrator::class,
            QualiteLibelleSupplementaireHydrator::class => QualiteLibelleSupplementaireHydrator::class,
        ],
    ],
);
