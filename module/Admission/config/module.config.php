<?php

namespace Admission;


use Admission\Controller\AdmissionController;
use Admission\Controller\AdmissionControllerFactory;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldsetFactory;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldsetFactory;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldsetFactory;
use Admission\Form\Fieldset\Validation\ValidationFieldset;
use Admission\Form\Fieldset\Validation\ValidationFieldsetFactory;
use Admission\Hydrator\IndividuHydrator;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Admission\AdmissionServiceFactory;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Financement\FinancementServiceFactory;
use Admission\Service\Individu\IndividuService;
use Admission\Service\Individu\IndividuServiceFactory;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Inscription\InscriptionServiceFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use UnicaenAuth\Guard\PrivilegeController;

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
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'ajouter',
                        'etudiant',
                        'inscription',
                        'financement',
                        'validation',
                        'confirmer',
                        'annuler',
                        'addInformationsEtudiant',
                        'addInformationsInscription',
                        'addInformationsFinancement',
                        'addInformationsJustificatifs'
                    ]
                ]
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
                        'action' => 'ajouter',
                        'controller' => AdmissionController::class,
                    ],
                ],
                'child_routes' => [
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:action',
                            'constraints' => [
                                /**
                                 * @see AdmissionController::ajouterAction()
                                 * @see AdmissionController::ajouterEtudiantAction()
                                 */
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
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

    'form_manager' => [
        'factories' => [
            EtudiantFieldset::class => EtudiantFieldsetFactory::class,
            InscriptionFieldset::class => InscriptionFieldsetFactory::class,
            FinancementFieldset::class => FinancementFieldsetFactory::class,
            ValidationFieldset::class => ValidationFieldsetFactory::class
        ],
    ],

    'hydrators' => [
        'factories' => [
        ],
    ],

    'service_manager' => [
        'factories' => [
            AdmissionService::class => AdmissionServiceFactory::class,
            FinancementService::class => FinancementServiceFactory::class,
            IndividuService::class => IndividuServiceFactory::class,
            InscriptionService::class => InscriptionServiceFactory::class
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
