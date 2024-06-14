<?php

namespace These;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use These\Controller\Factory\TheseSaisieControllerFactory;
use These\Controller\TheseRechercheController;
use These\Controller\TheseSaisieController;
use These\Fieldset\Confidentialite\ConfidentialiteHydrator;
use These\Fieldset\Confidentialite\ConfidentialiteHydratorFactory;
use These\Fieldset\Direction\DirectionHydrator;
use These\Fieldset\Direction\DirectionHydratorFactory;
use These\Fieldset\Encadrement\EncadrementHydrator;
use These\Fieldset\Encadrement\EncadrementHydratorFactory;
use These\Fieldset\Generalites\GeneralitesHydrator;
use These\Fieldset\Generalites\GeneralitesHydratorFactory;
use These\Fieldset\Structures\StructuresHydrator;
use These\Fieldset\Structures\StructuresHydratorFactory;
use These\Fieldset\Confidentialite\ConfidentialiteFieldset;
use These\Fieldset\Confidentialite\ConfidentialiteFieldsetFactory;
use These\Fieldset\Encadrement\EncadrementFieldset;
use These\Fieldset\Encadrement\EncadrementFieldsetFactory;
use These\Fieldset\Generalites\GeneralitesFieldset;
use These\Fieldset\Generalites\GeneralitesFieldsetFactory;
use These\Fieldset\Structures\StructuresFieldset;
use These\Fieldset\Structures\StructuresFieldsetFactory;
use These\Form\Confidentialite\ConfidentialiteForm;
use These\Form\Confidentialite\ConfidentialiteFormFactory;
use These\Form\Encadrement\EncadrementForm;
use These\Form\Encadrement\EncadrementFormFactory;
use These\Form\Generalites\GeneralitesForm;
use These\Form\Generalites\GeneralitesFormFactory;
use These\Form\Structures\StructuresForm;
use These\Form\Structures\StructuresFormFactory;
use These\Form\TheseFormsManager;
use These\Form\TheseFormsManagerFactory;
use These\Form\TheseSaisie\TheseSaisieForm;
use These\Form\TheseSaisie\TheseSaisieFormFactory;
use These\Form\TheseSaisie\TheseSaisieHydrator;
use These\Form\TheseSaisie\TheseSaisieHydratorFactory;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TheseSaisieController::class,
                    'action' => [
                        'saisie', // todo: deprecated
                        'ajouter',
                        'supprimer',
                        'generalites',
                        'direction',
                        'structures',
                        'encadrement',
                    ],
                    'roles' => 'Administrateur technique',
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'these' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/these',
                    'defaults' => [
                        'controller' => TheseRechercheController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'saisie' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/saisie[/:these]',
                            'defaults' => [
                                'controller' => TheseSaisieController::class,
                                'action' => 'saisie',
                            ],
                        ],
                        'may_terminate' => true,
                    ],

                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter',
                            'defaults' => [
                                /** @see TheseSaisieController::ajouterAction() */
                                'controller' => TheseSaisieController::class,
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                /** @see TheseSaisieController::generalitesAction() */
                                'controller' => TheseSaisieController::class,
                                'action' => 'generalites',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'generalites' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/generalites',
                                    'defaults' => [
                                        /** @see TheseSaisieController::generalitesAction() */
                                        'action' => 'generalites',
                                    ],
                                ],
                            ],
                            'direction' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/direction',
                                    'defaults' => [
                                        /** @see TheseSaisieController::directionAction() */
                                        'action' => 'direction',
                                    ],
                                ],
                            ],
                            'structures' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/structures',
                                    'defaults' => [
                                        /** @see TheseSaisieController::structuresAction() */
                                        'action' => 'structures',
                                    ],
                                ],
                            ],
                            'encadrement' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/encadrement',
                                    'defaults' => [
                                        /** @see TheseSaisieController::encadrementAction() */
                                        'action' => 'encadrement',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/supprimer/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                /** @see TheseSaisieController::supprimerAction() */
                                'controller' => TheseSaisieController::class,
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            TheseSaisieController::class => TheseSaisieControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            TheseSaisieForm::class => TheseSaisieFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            TheseSaisieHydrator::class => TheseSaisieHydratorFactory::class,

            ConfidentialiteHydrator::class => ConfidentialiteHydratorFactory::class,
            EncadrementHydrator::class => EncadrementHydratorFactory::class,
            GeneralitesHydrator::class => GeneralitesHydratorFactory::class,
            DirectionHydrator::class => DirectionHydratorFactory::class,
            StructuresHydrator::class => StructuresHydratorFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            TheseFormsManager::class => TheseFormsManagerFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [],
    ],
];