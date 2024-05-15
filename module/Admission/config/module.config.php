<?php

namespace Admission;


use Admission\Assertion\AdmissionAssertion;
use Admission\Assertion\AdmissionAssertionFactory;
use Admission\Controller\AdmissionController;
use Admission\Controller\AdmissionControllerFactory;
use Admission\Controller\AdmissionRechercheController;
use Admission\Controller\AdmissionRechercheControllerFactory;
use Admission\Event\AdmissionEventListener;
use Admission\Event\AdmissionEventListenerFactory;
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
use Admission\Service\Admission\AdmissionRechercheService;
use Admission\Service\Admission\AdmissionRechercheServiceFactory;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Admission\AdmissionServiceFactory;
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
use Application\Navigation\ApplicationNavigationFactory;
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
                            AdmissionPrivileges::ADMISSION_NOTIFIER_DOSSIER_INCOMPLET,
                            AdmissionPrivileges::ADMISSION_GENERER_RECAPITULATIF,
                            AdmissionPrivileges::ADMISSION_ACCEDER_RECAPITULATIF_DOSSIER,
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
                [
                    'controller' => AdmissionRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_AFFICHER_SON_DOSSIER_ADMISSION,
                        AdmissionPrivileges::ADMISSION_LISTER_MES_DOSSIERS_ADMISSION,
                        AdmissionPrivileges::ADMISSION_INITIALISER_ADMISSION
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admission',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => AdmissionRechercheController::class,
                    ],
                ],
                'child_routes' => [
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action/:individu',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'admission' => '[0-9]*'
                            ],
                            'defaults' => [
                                /**
                                 * @see AdmissionController::etudiantAction()
                                 * @see AdmissionController::inscriptionAction()
                                 * @see AdmissionController::financementAction()
                                 * @see AdmissionController::documentAction()
                                 * @see AdmissionController::enregistrerAction()
                                 * @see AdmissionController::supprimerAction()
                                 */
                                'controller' => AdmissionController::class,
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
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/rechercher-individu',
                            'defaults'    => [
                                'action' => 'rechercher-individu',
                                'controller' => AdmissionController::class,
                                /* @see AdmissionController::rechercherIndividuAction() */
                            ],
                        ],
                    ],
                    'recherche' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => AdmissionRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filters' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                        'controller' => AdmissionRechercheController::class,
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
            // DEPTH = 0
            'home' => [
                'pages' => [
                    /**
                     * Page pour le Candidat.
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MON_ADMISSION_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Admission',
                        'route' => 'admission',
                        'resource' => PrivilegeController::getResourceId(AdmissionRechercheController::class, 'index'),
                        'pages' => [
                        ],
                    ],

                    /**
                     * Page pour Dir, Codir.
                     */
                    ApplicationNavigationFactory::MES_ADMISSIONS_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Mes admissions',
                        'route' => 'admission',
                        'resource' => PrivilegeController::getResourceId(AdmissionRechercheController::class, 'index'),
                        'pages' => [
                        ],
                    ],
                    /**
                     * Page pour Dir, Codir.
                     */
                    ApplicationNavigationFactory::NOS_ADMISSIONS_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Nos admissions',
                        'route' => 'admission',
                        'resource' => PrivilegeController::getResourceId(AdmissionRechercheController::class, 'index'),
                        'pages' => [
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            AdmissionController::class => AdmissionControllerFactory::class,
            AdmissionRechercheController::class => AdmissionRechercheControllerFactory::class
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
            AdmissionRechercheService::class => AdmissionRechercheServiceFactory::class,

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
            '090_filepond' => "/vendor/filepond/filepond.css",
            '0100_filepond_pdf_preview' => "/vendor/filepond/filepond-plugin-pdf-preview.min.css",
        ],
        'head_scripts' => [
            '080_admission' => "/js/admission.js",
            '090_filepond' => "vendor/filepond/filepond.min.js",
            '0100_filepond_pdf_preview' => "vendor/filepond/filepond-plugin-pdf-preview.min.js",
            '0110_filepond-plugin-file-validate-type' => "vendor/filepond/filepond-plugin-file-validate-type.js",
        ],
    ],
);
