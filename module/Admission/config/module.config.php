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
use Admission\Form\ConventionFormationDoctorale\ConventionFormationDoctoraleForm;
use Admission\Form\ConventionFormationDoctorale\ConventionFormationDoctoraleFormFactory;
use Admission\Form\Fieldset\Document\DocumentFieldset;
use Admission\Form\Fieldset\Document\DocumentFieldsetFactory;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldsetFactory;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldsetFactory;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldsetFactory;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldsetFactory;
use Admission\Hydrator\Admission\AdmissionHydrator;
use Admission\Hydrator\Admission\AdmissionHydratorFactory;
use Admission\Hydrator\ConventionFormationDoctorale\ConventionFormationDoctoraleHydrator;
use Admission\Hydrator\ConventionFormationDoctorale\ConventionFormationDoctoraleHydratorFactory;
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
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Admission\AdmissionServiceFactory;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleService;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleServiceFactory;
use Admission\Service\Document\DocumentService;
use Admission\Service\Document\DocumentServiceFactory;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Etudiant\EtudiantServiceFactory;
use Admission\Service\Exporter\Recapitulatif\RecapitulatifExporter;
use Admission\Service\Exporter\Recapitulatif\RecapitulatifExporterFactory;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Financement\FinancementServiceFactory;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Inscription\InscriptionServiceFactory;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\TypeValidation\TypeValidationServiceFactory;
use Admission\Service\Url\UrlService;
use Admission\Service\Url\UrlServiceFactory;
use Admission\Service\Verification\VerificationService;
use Admission\Service\Verification\VerificationServiceFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
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
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_LISTER_MES_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION,
                            AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION,
                            AdmissionPrivileges::ADMISSION_HISTORISER,
                            AdmissionPrivileges::ADMISSION_VERIFIER,
                            AdmissionPrivileges::ADMISSION_ACCEDER_COMMENTAIRES,
                            AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES,
                            AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET,
                            AdmissionPrivileges::ADMISSION_GENERER_RECAPITULATIF,
                            AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION_DANS_LISTE
                        ],
                        'resources'  => ['Admission'],
                        'assertion'  => AdmissionAssertion::class,
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
                        'rechercher-individu'
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_LISTER_MES_DOSSIERS_ADMISSION,
                        AdmissionPrivileges::ADMISSION_INITIALISER_ADMISSION
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'etudiant',
                        'inscription',
                        'financement',
                        'document',
                        'enregistrer',
                        'generer-statut-dossier'
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_INITIALISER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_AFFICHER_TOUS_DOSSIERS_ADMISSION,
                        AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION,
                        AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'annuler',
                        'supprimer',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUS_DOSSIERS_ADMISSION,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'notifier-commentaires-ajoutes',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_NOTIFIER_COMMENTAIRES_AJOUTES,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'notifier-dossier-complet',
                        'notifier-dossier-incomplet',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'generer-recapitulatif',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_GENERER_RECAPITULATIF,
                    ],
                    'assertion' => AdmissionAssertion::class,
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
                                 * @see AdmissionController::enregistrerAction()
                                 * @see AdmissionController::supprimerAction()
                                 */
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'admission' => '[0-9]*'
                            ],
                        ],
                    ],
                    'notifier-commentaires-ajoutes' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/notifier-commentaires-ajoutes/:admission',
                            'constraints' => [
                                'admission' => '[0-9]*'
                            ],
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => 'notifier-commentaires-ajoutes',
                                /* @see AdmissionController::notifierCommentairesAjoutesAction() */
                            ],
                        ],
                    ],
                    'notifier-dossier-incomplet' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/notifier-dossier-incomplet/:admission',
                            'constraints' => [
                                'admission' => '[0-9]*'
                            ],
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => 'notifier-dossier-incomplet',
                                /* @see AdmissionController::notifierDossierIncompletAction() */
                            ],
                        ],
                    ],
                    'generer-recapitulatif' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/generer-recapitulatif/:admission/signature-presidence',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action'     => 'generer-recapitulatif',
                                /* @see AdmissionController::genererRecapitulatifAction() */
                            ],
                        ],
                    ],
                    'generer-statut-dossier' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/generer-statut-dossier/:admission',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action'     => 'generer-statut-dossier',
                                /* @see AdmissionController::genererStatutDossierAction() */
                            ],
                        ],
                    ],
                    'rechercher-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/rechercher-individu',
                            'defaults'    => [
                                'action' => 'rechercher-individu',
                                /* @see AdmissionController::rechercherIndividuAction() */
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
        ],
    ],

    'form_elements' => [
        'factories' => [
            AdmissionForm::class => AdmissionFormFactory::class,
            EtudiantFieldset::class => EtudiantFieldsetFactory::class,
            InscriptionFieldset::class => InscriptionFieldsetFactory::class,
            FinancementFieldset::class => FinancementFieldsetFactory::class,
            VerificationFieldset::class => VerificationFieldsetFactory::class,
            DocumentFieldset::class => DocumentFieldsetFactory::class,
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
            DocumentHydrator::class => DocumentHydratorFactory::class,
            ConventionFormationDoctoraleHydrator::class => ConventionFormationDoctoraleHydratorFactory::class
        ],
    ],

    'service_manager' => [
        'factories' => [
            AdmissionService::class => AdmissionServiceFactory::class,
            FinancementService::class => FinancementServiceFactory::class,
            EtudiantService::class => EtudiantServiceFactory::class,
            InscriptionService::class => InscriptionServiceFactory::class,
            TypeValidationService::class => TypeValidationServiceFactory::class,
            DocumentService::class => DocumentServiceFactory::class,
            VerificationService::class => VerificationServiceFactory::class,

            AdmissionEventListener::class => AdmissionEventListenerFactory::class,

            AdmissionAssertion::class => AdmissionAssertionFactory::class,

            UrlService::class => UrlServiceFactory::class,

            RecapitulatifExporter::class => RecapitulatifExporterFactory::class
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
