<?php

namespace Admission;


use Admission\Assertion\AdmissionAssertion;
use Admission\Assertion\AdmissionAssertionFactory;
use Admission\Assertion\Validation\AdmissionValidationAssertion;
use Admission\Assertion\Validation\AdmissionValidationAssertionFactory;
use Admission\Config\ModuleConfig;
use Admission\Config\ModuleConfigFactory;
use Admission\Controller\AdmissionController;
use Admission\Controller\AdmissionControllerFactory;
use Admission\Controller\Validation\AdmissionValidationController;
use Admission\Controller\Validation\AdmissionValidationControllerFactory;
use Admission\Event\AdmissionEventListener;
use Admission\Event\AdmissionEventListenerFactory;
use Admission\Event\Validation\AdmissionValidationEventListener;
use Admission\Event\Validation\AdmissionValidationEventListenerFactory;
use Admission\Form\Admission\AdmissionForm;
use Admission\Form\Admission\AdmissionFormFactory;
use Admission\Form\Fieldset\Document\DocumentFieldset;
use Admission\Form\Fieldset\Document\DocumentFieldsetFactory;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldsetFactory;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldsetFactory;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldsetFactory;
use Admission\Form\Fieldset\Validation\AdmissionValidationFieldset;
use Admission\Form\Fieldset\Validation\AdmissionValidationFieldsetFactory;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldsetFactory;
use Admission\Hydrator\Admission\AdmissionHydrator;
use Admission\Hydrator\Admission\AdmissionHydratorFactory;
use Admission\Hydrator\Document\DocumentHydrator;
use Admission\Hydrator\Document\DocumentHydratorFactory;
use Admission\Hydrator\Etudiant\EtudiantHydrator;
use Admission\Hydrator\Etudiant\EtudiantHydratorFactory;
use Admission\Hydrator\Financement\FinancementHydrator;
use Admission\Hydrator\Financement\FinancementHydratorFactory;
use Admission\Hydrator\Inscription\InscriptionHydrator;
use Admission\Hydrator\Inscription\InscriptionHydratorFactory;
use Admission\Hydrator\Validation\AdmissionValidationHydrator;
use Admission\Hydrator\Validation\AdmissionValidationHydratorFactory;
use Admission\Hydrator\Verification\VerificationHydrator;
use Admission\Hydrator\Verification\VerificationHydratorFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Rule\Operation\AdmissionOperationRuleFactory;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRule;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRuleFactory;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Admission\AdmissionServiceFactory;
use Admission\Service\Document\DocumentService;
use Admission\Service\Document\DocumentServiceFactory;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Etudiant\EtudiantServiceFactory;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Financement\FinancementServiceFactory;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Inscription\InscriptionServiceFactory;
use Admission\Service\Operation\AdmissionOperationService;
use Admission\Service\Operation\AdmissionOperationServiceFactory;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\TypeValidation\TypeValidationServiceFactory;
use Admission\Service\Validation\AdmissionValidationService;
use Admission\Service\Validation\AdmissionValidationServiceFactory;
use Admission\Service\Verification\VerificationService;
use Admission\Service\Verification\VerificationServiceFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Admission\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Admission/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Admission' => [],
                'AdmissionValidation' => []
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_LISTER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_LISTER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_HISTORISER,
                            AdmissionPrivileges::ADMISSION_VERIFIER,
                        ],
                        'resources'  => ['Admission'],
                        'assertion'  => AdmissionAssertion::class,
                    ],
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_VALIDER_SIEN,
                            AdmissionPrivileges::ADMISSION_VALIDER_TOUT,
                            AdmissionPrivileges::ADMISSION_DEVALIDER_SIEN,
                            AdmissionPrivileges::ADMISSION_DEVALIDER_TOUT
                        ],
                        'resources'  => ['AdmissionValidation'],
                        'assertion'  => AdmissionValidationAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'index',
                        'confirmer',
                        'enregistrer',
                        'rechercher-individu',
                        'enregistrer-document',
                        'supprimer-document',
                        'telecharger-document'
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_LISTER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_LISTER_TOUS_DOSSIERS_ADMISSION,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_LISTER_TOUS_DOSSIERS_ADMISSION,
                    ],
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'etudiant',
                        'inscription',
                        'financement',
                        'document',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'annuler',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION,
                    ],
                ],
                [
                    'controller' => AdmissionValidationController::class,
                    'action' => [
                        'valider',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_VALIDER_TOUT,
                        AdmissionPrivileges::ADMISSION_VALIDER_SIEN,
                    ],
                    'assertion' => AdmissionValidationAssertion::class,
                ],
                [
                    'controller' => AdmissionValidationController::class,
                    'action' => [
                        'devalider',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_DEVALIDER_TOUT,
                        AdmissionPrivileges::ADMISSION_DEVALIDER_SIEN,
                    ],
                    'assertion' => AdmissionValidationAssertion::class,
                ],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admission',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => AdmissionController::class,
                    ],
                ],
                'child_routes' => [
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:individu',
                            'constraints' => [
                                /**
                                 * @see AdmissionController::etudiantAction()
                                 * @see AdmissionController::inscriptionAction()
                                 * @see AdmissionController::financementAction()
                                 * @see AdmissionController::documentAction()
                                 */
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'admission' => '[0-9]*'
                            ],
                        ],
                    ],
                    'valider' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/valider/:admission/type/:typeValidation',
                            'constraints' => [
                                'admission' => '\d+',
                                'typeValidation' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => AdmissionValidationController::class,
                                'action' => 'valider',
                                /* @see AdmissionValidationController::validerAction() */
                            ],
                        ],
                    ],
                    'devalider' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/devalider/:admissionValidation',
                            'constraints' => [
                                'admissionValidation' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => AdmissionValidationController::class,
                                'action' => 'devalider',
                                /* @see AdmissionValidationController::devaliderAction() */
                            ],
                        ],
                    ],
                    /**
                     * @see AdmissionController::rechercherIndividuAction()
                     */
                    'rechercher-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/rechercher-individu',
                            'defaults'    => [
                                'action' => 'rechercher-individu',
                            ],
                        ],
                    ],
                    /**
                     * @see AdmissionController::enregistrerDocumentAction()
                     */
                    'enregistrer-document' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/enregistrer-document',
                            'defaults'    => [
                                'action' => 'enregistrer-document',
                            ],
                        ],
                    ],
                    /**
                     * @see AdmissionController::supprimerDocumentAction()
                     */
                    'supprimer-document' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/supprimer-document',
                            'defaults'    => [
                                'action' => 'supprimer-document',
                            ],
                        ],
                    ],
                    /**
                     * @see AdmissionController::telechargerDocumentAction()
                     */
                    'telecharger-document' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/telecharger-document',
                            'defaults'    => [
                                'action' => 'telecharger-document',
                            ],
                        ],
                    ],
                ],
            ],

        ],
    ],

    'controllers' => [
        'factories' => [
            AdmissionController::class => AdmissionControllerFactory::class,
            AdmissionValidationController::class => AdmissionValidationControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            AdmissionForm::class => AdmissionFormFactory::class,
            EtudiantFieldset::class => EtudiantFieldsetFactory::class,
            InscriptionFieldset::class => InscriptionFieldsetFactory::class,
            FinancementFieldset::class => FinancementFieldsetFactory::class,
            AdmissionValidationFieldset::class => AdmissionValidationFieldsetFactory::class,
            VerificationFieldset::class => VerificationFieldsetFactory::class,
            DocumentFieldset::class => DocumentFieldsetFactory::class
        ],
    ],

    'hydrators' => [
        'factories' => [
            AdmissionHydrator::class => AdmissionHydratorFactory::class,
            EtudiantHydrator::class => EtudiantHydratorFactory::class,
            InscriptionHydrator::class => InscriptionHydratorFactory::class,
            FinancementHydrator::class => FinancementHydratorFactory::class,
            AdmissionValidationHydrator::class => AdmissionValidationHydratorFactory::class,
            VerificationHydrator::class => VerificationHydratorFactory::class,
            DocumentHydrator::class => DocumentHydratorFactory::class
        ],
    ],

    'service_manager' => [
        'factories' => [
            ModuleConfig::class => ModuleConfigFactory::class,

            AdmissionService::class => AdmissionServiceFactory::class,
            FinancementService::class => FinancementServiceFactory::class,
            EtudiantService::class => EtudiantServiceFactory::class,
            InscriptionService::class => InscriptionServiceFactory::class,
            TypeValidationService::class => TypeValidationServiceFactory::class,
            DocumentService::class => DocumentServiceFactory::class,
            VerificationService::class => VerificationServiceFactory::class,
            AdmissionValidationService::class => AdmissionValidationServiceFactory::class,
            AdmissionOperationService::class => AdmissionOperationServiceFactory::class,

            AdmissionEventListener::class => AdmissionEventListenerFactory::class,
            AdmissionValidationEventListener::class => AdmissionValidationEventListenerFactory::class,

            OperationAttendueNotificationRule::class => OperationAttendueNotificationRuleFactory::class,
            AdmissionOperationRule::class => AdmissionOperationRuleFactory::class,

            AdmissionValidationAssertion::class => AdmissionValidationAssertionFactory::class,
            AdmissionAssertion::class => AdmissionAssertionFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'public_files' => [
        'inline_scripts' => [
        ],
        'stylesheets' => [
            '080_admission' => '/css/admission.css',
        ],
        'head_scripts' => [
            '080_uploader' => "/js/admission.js",
        ],
    ],
);
