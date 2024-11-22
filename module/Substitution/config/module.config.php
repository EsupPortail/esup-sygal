<?php

namespace Substitution;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Substitution\Controller\DoublonController;
use Substitution\Controller\DoublonControllerFactory;
use Substitution\Controller\ForeignKeyController;
use Substitution\Controller\ForeignKeyControllerFactory;
use Substitution\Controller\IndexController;
use Substitution\Controller\IndexControllerFactory;
use Substitution\Controller\LogController;
use Substitution\Controller\LogControllerFactory;
use Substitution\Controller\SubstitutionController;
use Substitution\Controller\SubstitutionControllerFactory;
use Substitution\Controller\TriggerController;
use Substitution\Controller\TriggerControllerFactory;
use Substitution\Provider\Privilege\SubstitutionPrivileges;
use Substitution\Service\Doublon\Doctorant\DoctorantDoublonService;
use Substitution\Service\Doublon\Doctorant\DoctorantDoublonServiceFactory;
use Substitution\Service\Doublon\DoublonService;
use Substitution\Service\Doublon\DoublonServiceFactory;
use Substitution\Service\Doublon\EcoleDoctorale\EcoleDoctoraleDoublonService;
use Substitution\Service\Doublon\EcoleDoctorale\EcoleDoctoraleDoublonServiceFactory;
use Substitution\Service\Doublon\Etablissement\EtablissementDoublonService;
use Substitution\Service\Doublon\Etablissement\EtablissementDoublonServiceFactory;
use Substitution\Service\Doublon\Individu\IndividuDoublonService;
use Substitution\Service\Doublon\Individu\IndividuDoublonServiceFactory;
use Substitution\Service\Doublon\Structure\StructureDoublonService;
use Substitution\Service\Doublon\Structure\StructureDoublonServiceFactory;
use Substitution\Service\Doublon\UniteRecherche\UniteRechercheDoublonService;
use Substitution\Service\Doublon\UniteRecherche\UniteRechercheDoublonServiceFactory;
use Substitution\Service\ForeignKey\ForeignKeyService;
use Substitution\Service\ForeignKey\ForeignKeyServiceFactory;
use Substitution\Service\Log\LogService;
use Substitution\Service\Log\LogServiceFactory;
use Substitution\Service\Substitution\Doctorant\DoctorantSubstitutionService;
use Substitution\Service\Substitution\Doctorant\DoctorantSubstitutionServiceFactory;
use Substitution\Service\Substitution\EcoleDoctorale\EcoleDoctoraleSubstitutionService;
use Substitution\Service\Substitution\EcoleDoctorale\EcoleDoctoraleSubstitutionServiceFactory;
use Substitution\Service\Substitution\Etablissement\EtablissementSubstitutionService;
use Substitution\Service\Substitution\Etablissement\EtablissementSubstitutionServiceFactory;
use Substitution\Service\Substitution\Individu\IndividuSubstitutionService;
use Substitution\Service\Substitution\Individu\IndividuSubstitutionServiceFactory;
use Substitution\Service\Substitution\Structure\StructureSubstitutionService;
use Substitution\Service\Substitution\Structure\StructureSubstitutionServiceFactory;
use Substitution\Service\Substitution\SubstitutionService;
use Substitution\Service\Substitution\SubstitutionServiceFactory;
use Substitution\Service\Substitution\UniteRecherche\UniteRechercheSubstitutionService;
use Substitution\Service\Substitution\UniteRecherche\UniteRechercheSubstitutionServiceFactory;
use Substitution\Service\Trigger\TriggerService;
use Substitution\Service\Trigger\TriggerServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'accueil',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => SubstitutionController::class,
                    'action' => [
                        'accueil',
                        'lister',
                        'voir',
                        'voirSubstitue',
                        'voirSubstituant',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => SubstitutionController::class,
                    'action' => [
                        'creer',
                        'creerManu',
                        'modifier',
                        'modifierSubstituant',
                        'ajouterSubstitue',
                        'retirerSubstitue',
                        'rechercherSubstituable',
                        'rechercherSubstituableManu',
                        'ajouterSubstitueManu',
                        'voirSubstituable',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_MODIFIER,
                    ],
                ],
                [
                    'controller' => DoublonController::class,
                    'action' => [
                        'accueil',
                        'lister',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => ForeignKeyController::class,
                    'action' => [
                        'accueil',
                        'lister',
                        'lister-enregistrements-lies',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => TriggerController::class,
                    'action' => [
                        'accueil',
                        'lister',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => LogController::class,
                    'action' => [
                        'accueil',
                        'lister',
                    ],
                    'privileges' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'substitution' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/substitution',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'accueil',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'substitution' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/substitution',
                            'defaults' => [
                                'controller' => SubstitutionController::class,
                                'action' => 'accueil',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'lister' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/lister/:type',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        'action' => 'lister',
                                    ],
                                ],
                            ],
                            'voir' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type/voir/:id',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                        'id' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'voir',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'modifier' => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/modifier',
                                            'defaults' => [
                                                /** @see SubstitutionController::modifierAction() */
                                                'action' => 'modifier',
                                            ],
                                        ],
                                    ],
                                    'modifier-substituant' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/modifier-substituant',
                                            'defaults' => [
                                                /** @see SubstitutionController::modifierSubstituantAction() */
                                                'action' => 'modifierSubstituant',
                                            ],
                                        ],
                                    ],
                                    'ajouter-substitue' => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/ajouter-substitue',
                                            'defaults' => [
                                                /** @see SubstitutionController::ajouterSubstitueAction() */
                                                'action' => 'ajouterSubstitue',
                                            ],
                                        ],
                                    ],
                                    'rechercher-substituable' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/rechercher-substituable',
                                            'defaults' => [
                                                /** @see SubstitutionController::rechercherSubstituableAction() */
                                                'action' => 'rechercherSubstituable',
                                            ],
                                        ],
                                    ],
                                    'retirer-substitue' => [
                                        'type' => Segment::class,
                                        'options' => [
                                            'route' => '/retirer-substitue[/:substitue]',
                                            'constraints' => [
                                                'substitue' => '\d+',
                                            ],
                                            'defaults' => [
                                                /** @see SubstitutionController::retirerSubstitueAction() */
                                                'action' => 'retirerSubstitue',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'creer' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type/creer/substituable/:substituableId/npd/:npd',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                        'substituableId' => '\d+',
                                    ],
                                    'defaults' => [
                                        /** @see SubstitutionController::creerAction() */
                                        'action' => 'creer',
                                    ],
                                ],
                            ],
                            'creer-manu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type/creer-manu/substituables/:substituableId',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                        'substituableId' => '\d+',
                                    ],
                                    'defaults' => [
                                        /** @see SubstitutionController::creerManuAction() */
                                        'action' => 'creerManu',
                                    ],
                                ],
                            ],
                            'rechercher-substituable-manu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type/rechercher-substituable-manu/:npd',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        /** @see SubstitutionController::rechercherSubstituableAction() */
                                        'action' => 'rechercherSubstituable',
                                    ],
                                ],
                            ],
                            'ajouter-substitue-manu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type/ajouter-substitue-manu/:npd',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        /** @see SubstitutionController::ajouterSubstitueManuAction() */
                                        'action' => 'ajouterSubstitueManu',
                                    ],
                                ],
                            ],
                            'voir-substituable' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/:type/voir-substituable/[:substituableId]',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                        'substituableId' => '\d+',
                                    ],
                                    'defaults' => [
                                        /** @see SubstitutionController::voirSubstituableAction() */
                                        'action' => 'voirSubstituable',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'doublon' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/doublon',
                            'defaults' => [
                                /** @see DoublonController::accueilAction() */
                                'controller' => DoublonController::class,
                                'action' => 'accueil',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'lister' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/lister/:type',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        /** @see DoublonController::listerAction() */
                                        'action' => 'lister',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'foreign-key' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/foreign-key',
                            'defaults' => [
                                'controller' => ForeignKeyController::class,
                                'action' => 'accueil',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'lister' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/lister/:type',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        'action' => 'lister',
                                    ],
                                ],
                            ],
                            'lister-enregistrements-lies' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/lister-enregistrements-lies/:type/:id',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                        'id' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'lister-enregistrements-lies',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'trigger' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/trigger',
                            'defaults' => [
                                'controller' => TriggerController::class,
                                'action' => 'accueil',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'lister' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/lister/:type',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        'action' => 'lister',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'log' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/log',
                            'defaults' => [
                                'controller' => LogController::class,
                                'action' => 'accueil',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'lister' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/lister/:type',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        'action' => 'lister',
                                    ],
                                ],
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
                            '-----------substitution-divider' => [
                                'label' => null,
                                'order' => 69,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            'substitution' => [
                                'label' => 'Module Substitutions',
                                'route' => 'substitution',
                                'icon' => 'fas fa-object-group',
                                'resource' => PrivilegeController::getResourceId(SubstitutionController::class, 'accueil'),
                                'order' => 70,
                                'pages' => [
                                    'substitution' => [
                                        'label' => 'Substitutions existantes',
                                        'route' => 'substitution/substitution',
                                        'pages' => [
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/substitution/lister',
                                                'params' => ['type' => Constants::TYPE_structure],
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/substitution/lister',
                                                'params' => ['type' => Constants::TYPE_etablissement],
                                            ],
                                            'ecole_doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/substitution/lister',
                                                'params' => ['type' => Constants::TYPE_ecole_doct],
                                            ],
                                            'unite-rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/substitution/lister',
                                                'params' => ['type' => Constants::TYPE_unite_rech],
                                            ],
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/substitution/lister',
                                                'params' => ['type' => Constants::TYPE_individu],
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/substitution/lister',
                                                'params' => ['type' => Constants::TYPE_doctorant],
                                            ],
                                        ],
                                    ],
                                    'doublon' => [
                                        'label' => 'Substitutions possibles',
                                        'route' => 'substitution/doublon',
                                        'pages' => [
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/doublon/lister',
                                                'params' => ['type' => Constants::TYPE_structure],
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/doublon/lister',
                                                'params' => ['type' => Constants::TYPE_etablissement],
                                            ],
                                            'ecole_doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/doublon/lister',
                                                'params' => ['type' => Constants::TYPE_ecole_doct],
                                            ],
                                            'unite_rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/doublon/lister',
                                                'params' => ['type' => Constants::TYPE_unite_rech],
                                            ],
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/doublon/lister',
                                                'params' => ['type' => Constants::TYPE_individu],
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/doublon/lister',
                                                'params' => ['type' => Constants::TYPE_doctorant],
                                            ],
                                        ],
                                    ],
                                    'foreign-key' => [
                                        'label' => 'Clés étrangères',
                                        'route' => 'substitution/foreign-key',
                                        'pages' => [
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/foreign-key/lister',
                                                'params' => ['type' => Constants::TYPE_structure],
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/foreign-key/lister',
                                                'params' => ['type' => Constants::TYPE_etablissement],
                                            ],
                                            'ecole_doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/foreign-key/lister',
                                                'params' => ['type' => Constants::TYPE_ecole_doct],
                                            ],
                                            'unite_rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/foreign-key/lister',
                                                'params' => ['type' => Constants::TYPE_unite_rech],
                                            ],
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/foreign-key/lister',
                                                'params' => ['type' => Constants::TYPE_individu],
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/foreign-key/lister',
                                                'params' => ['type' => Constants::TYPE_doctorant],
                                            ],
                                        ],
                                    ],
                                    'trigger' => [
                                        'label' => 'Triggers',
                                        'route' => 'substitution/trigger',
                                        'pages' => [
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/trigger/lister',
                                                'params' => ['type' => Constants::TYPE_structure],
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/trigger/lister',
                                                'params' => ['type' => Constants::TYPE_etablissement],
                                            ],
                                            'ecole_doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/trigger/lister',
                                                'params' => ['type' => Constants::TYPE_ecole_doct],
                                            ],
                                            'unite_rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/trigger/lister',
                                                'params' => ['type' => Constants::TYPE_unite_rech],
                                            ],
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/trigger/lister',
                                                'params' => ['type' => Constants::TYPE_individu],
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/trigger/lister',
                                                'params' => ['type' => Constants::TYPE_doctorant],
                                            ],
                                        ],
                                    ],
                                    'log' => [
                                        'label' => 'Logs',
                                        'route' => 'substitution/log',
                                        'pages' => [
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/log/lister',
                                                'params' => ['type' => Constants::TYPE_individu],
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/log/lister',
                                                'params' => ['type' => Constants::TYPE_doctorant],
                                            ],
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/log/lister',
                                                'params' => ['type' => Constants::TYPE_structure],
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/log/lister',
                                                'params' => ['type' => Constants::TYPE_etablissement],
                                            ],
                                            'ecole_doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/log/lister',
                                                'params' => ['type' => Constants::TYPE_ecole_doct],
                                            ],
                                            'unite-rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/log/lister',
                                                'params' => ['type' => Constants::TYPE_unite_rech],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
            SubstitutionController::class => SubstitutionControllerFactory::class,
            DoublonController::class => DoublonControllerFactory::class,
            ForeignKeyController::class => ForeignKeyControllerFactory::class,
            TriggerController::class => TriggerControllerFactory::class,
            LogController::class => LogControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            SubstitutionService::class => SubstitutionServiceFactory::class,
            IndividuSubstitutionService::class => IndividuSubstitutionServiceFactory::class,
            DoctorantSubstitutionService::class => DoctorantSubstitutionServiceFactory::class,
            StructureSubstitutionService::class => StructureSubstitutionServiceFactory::class,
            EtablissementSubstitutionService::class => EtablissementSubstitutionServiceFactory::class,
            EcoleDoctoraleSubstitutionService::class => EcoleDoctoraleSubstitutionServiceFactory::class,
            UniteRechercheSubstitutionService::class => UniteRechercheSubstitutionServiceFactory::class,

            DoublonService::class => DoublonServiceFactory::class,
            IndividuDoublonService::class => IndividuDoublonServiceFactory::class,
            DoctorantDoublonService::class => DoctorantDoublonServiceFactory::class,
            StructureDoublonService::class => StructureDoublonServiceFactory::class,
            EtablissementDoublonService::class => EtablissementDoublonServiceFactory::class,
            EcoleDoctoraleDoublonService::class => EcoleDoctoraleDoublonServiceFactory::class,
            UniteRechercheDoublonService::class => UniteRechercheDoublonServiceFactory::class,

            ForeignKeyService::class => ForeignKeyServiceFactory::class,
            TriggerService::class => TriggerServiceFactory::class,
            LogService::class => LogServiceFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'public_files' => [
        'stylesheets' => [
            '080_substitution' => '/css/substitution.css',
        ],
    ],
];