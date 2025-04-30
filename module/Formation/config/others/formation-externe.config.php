<?php

namespace Formation;

use Formation\Assertion\FormationExterne\FormationExterneAssertion;
use Formation\Assertion\FormationExterne\FormationExterneAssertionFactory;
use Formation\Controller\FormationExterneController;
use Formation\Controller\FormationExterneControllerFactory;
use Formation\Form\Formation\FormationExterne\FormationExterneForm;
use Formation\Form\Formation\FormationExterne\FormationExterneFormFactory;
use Formation\Provider\Privilege\FormationPrivileges;
use Formation\Service\Formation\FormationExterne\FormationExterneService;
use Formation\Service\Formation\FormationExterne\FormationExterneServiceFactory;
use Formation\Service\Validation\ValidationFormationExterne\ValidationFormationExterneService;
use Formation\Service\Validation\ValidationFormationExterne\ValidationFormationExterneServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'FormationExterne' => []
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            FormationPrivileges::FORMATION_FORMATIONEXTERNE_MODIFIER,
                            FormationPrivileges::FORMATION_FORMATIONEXTERNE_HISTORISER,
                            FormationPrivileges::FORMATION_FORMATIONEXTERNE_SUPPRIMER,
                            FormationPrivileges::FORMATION_FORMATIONEXTERNE_GERER_VALIDATION,
                        ],
                        'resources' => ['FormationExterne'],
                        'assertion'  => FormationExterneAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_AJOUTER,
                    ],
                ],
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_MODIFIER,
                    ],
                    'assertion' => FormationExterneAssertion::class,
                ],
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_HISTORISER,
                    ],
                    'assertion' => FormationExterneAssertion::class,
                ],
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'supprimer',
                        'supprimer-attestation',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_SUPPRIMER,
                    ],
                    'assertion' => FormationExterneAssertion::class,
                ],
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'telecharger-attestation'
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_TELECHARGER_ATTESTATION,
                    ],
                    'assertion' => FormationExterneAssertion::class,
                ],
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'refuser-formation-externe',
                        'valider-formation-externe',
                        'revoquer-formation-externe'
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_GERER_VALIDATION,
                    ],
                    'assertion' => FormationExterneAssertion::class,
                ],
                [
                    'controller' => FormationExterneController::class,
                    'action' => [
                        'notifier-formation-externe-ajoutee'
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_FORMATIONEXTERNE_NOTIFIER_FORMATION_AJOUTEE,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'formation' => [
                        'child_routes' => [
                            'ajouter-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter-formation-externe/:doctorant',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::ajouterAction() */
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'modifier-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier-formation-externe/:formationExterne',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::modifierAction() */
                                        'action'     => 'modifier',
                                    ],
                                ],
                            ],
                            'historiser-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser-formation-externe/:formationExterne',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::historiserAction() */
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer-formation-externe/:formationExterne',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::restaurerAction() */
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer-formation-externe/:formationExterne',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::supprimerAction() */
                                        'action'     => 'supprimer',
                                    ],
                                ],
                            ],
                            'supprimer-attestation-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer-attestation-formation-externe/:formationExterne/:fichier',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::supprimerAttestationAction() */
                                        'action'     => 'supprimer-attestation',
                                    ],
                                ],
                            ],
                            'telecharger-attestation-formation-externe' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/telecharger-attestation-formation-externe/:formationExterne',
                                    'defaults' => [
                                        'controller' => FormationExterneController::class,
                                        /** @see FormationExterneController::telechargerAttestationAction() */
                                        'action'     => 'telecharger-attestation',
                                    ],
                                ],
                            ],
                            'valider-formation-externe' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/formation-externe/valider/:formationExterne',
                                    'defaults' => [
                                        /** @see FormationExterneController::validerFormationExterneAction() */
                                        'controller' => FormationExterneController::class,
                                        'action' => 'valider-formation-externe',
                                    ],
                                ],
                            ],
                            'refuser-formation-externe' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/formation-externe/refuser/:formationExterne',
                                    'defaults' => [
                                        /** @see FormationExterneController::refuserFormationExterneAction() */
                                        'controller' => FormationExterneController::class,
                                        'action' => 'refuser-formation-externe',
                                    ],
                                ],
                            ],
                            'revoquer-formation-externe' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/formation-externe/revoquer/:formationExterne',
                                    'defaults' => [
                                        /** @see FormationExterneController::revoquerFormationExterneAction() */
                                        'controller' => FormationExterneController::class,
                                        'action' => 'revoquer-formation-externe',
                                    ],
                                ],
                            ],
                            'notifier-formation-externe-ajoutee' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/formation-externe/notifier-ajout/:these',
                                    'defaults' => [
                                        /** @see FormationExterneController::notifierFormationExterneAjouteeAction() */
                                        'controller' => FormationExterneController::class,
                                        'action' => 'notifier-formation-externe-ajoutee',
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
            FormationExterneService::class => FormationExterneServiceFactory::class,
            FormationExterneAssertion::class => FormationExterneAssertionFactory::class,

            ValidationFormationExterneService::class => ValidationFormationExterneServiceFactory::class
        ],
    ],
    'controllers'     => [
        'factories' => [
            FormationExterneController::class => FormationExterneControllerFactory::class
        ],
    ],
    'form_elements' => [
        'factories' => [
            FormationExterneForm::class => FormationExterneFormFactory::class
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ]
];