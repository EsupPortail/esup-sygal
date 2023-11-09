<?php

namespace Admission;


use Admission\Assertion\AdmissionAssertion;
use Admission\Assertion\AdmissionAssertionFactory;
use Admission\Controller\AdmissionController;
use Admission\Controller\AdmissionControllerFactory;
use Admission\Entity\Db\Repository\IndividuRepositoryFactory;
use Admission\Form\Admission\AdmissionForm;
use Admission\Form\Admission\AdmissionFormFactory;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldsetFactory;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldsetFactory;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldsetFactory;
use Admission\Form\Fieldset\Validation\ValidationFieldset;
use Admission\Form\Fieldset\Validation\ValidationFieldsetFactory;
use Admission\Form\Fieldset\Verification\VerificationFieldset;
use Admission\Form\Fieldset\Verification\VerificationFieldsetFactory;
use Admission\Hydrator\Admission\AdmissionHydrator;
use Admission\Hydrator\Admission\AdmissionHydratorFactory;
use Admission\Hydrator\Etudiant\EtudiantHydrator;
use Admission\Hydrator\Etudiant\EtudiantHydratorFactory;
use Admission\Hydrator\Financement\FinancementHydrator;
use Admission\Hydrator\Financement\FinancementHydratorFactory;
use Admission\Hydrator\Inscription\InscriptionHydrator;
use Admission\Hydrator\Inscription\InscriptionHydratorFactory;
use Admission\Hydrator\Validation\ValidationHydrator;
use Admission\Hydrator\Validation\ValidationHydratorFactory;
use Admission\Hydrator\Verification\VerificationHydrator;
use Admission\Hydrator\Verification\VerificationHydratorFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
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
use Admission\Service\Validation\ValidationService;
use Admission\Service\Validation\ValidationServiceFactory;
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
                        'rechercher-individu'
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
                        'validation',
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
                                 * @see AdmissionController::validationAction()
                                 */
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'admission' => '[0-9]*'
                            ],
                        ],
                    ],
                    'rechercher-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/rechercher-individu',
                            'defaults'    => [
                                'action' => 'rechercher-individu',
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
            ValidationFieldset::class => ValidationFieldsetFactory::class,
            VerificationFieldset::class => VerificationFieldsetFactory::class
        ],
    ],

    'hydrators' => [
        'factories' => [
            AdmissionHydrator::class => AdmissionHydratorFactory::class,
            EtudiantHydrator::class => EtudiantHydratorFactory::class,
            InscriptionHydrator::class => InscriptionHydratorFactory::class,
            FinancementHydrator::class => FinancementHydratorFactory::class,
            ValidationHydrator::class => ValidationHydratorFactory::class,
            VerificationHydrator::class => VerificationHydratorFactory::class
        ],
    ],

    'service_manager' => [
        'factories' => [
            AdmissionService::class => AdmissionServiceFactory::class,
            FinancementService::class => FinancementServiceFactory::class,
            EtudiantService::class => EtudiantServiceFactory::class,
            InscriptionService::class => InscriptionServiceFactory::class,
            ValidationService::class => ValidationServiceFactory::class,
            DocumentService::class => DocumentServiceFactory::class,
            VerificationService::class => VerificationServiceFactory::class,
            AdmissionAssertion::class => AdmissionAssertionFactory::class
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
