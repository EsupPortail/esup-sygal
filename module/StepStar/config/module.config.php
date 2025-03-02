<?php

namespace StepStar;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use StepStar\Controller\Envoi\EnvoiConsoleController;
use StepStar\Controller\Envoi\EnvoiConsoleControllerFactory;
use StepStar\Controller\Envoi\EnvoiController;
use StepStar\Controller\Envoi\EnvoiControllerFactory;
use StepStar\Controller\Generate\GenerateConsoleController;
use StepStar\Controller\Generate\GenerateConsoleControllerFactory;
use StepStar\Controller\Generate\GenerateController;
use StepStar\Controller\Generate\GenerateControllerFactory;
use StepStar\Controller\IndexController;
use StepStar\Controller\IndexControllerFactory;
use StepStar\Controller\Log\LogController;
use StepStar\Controller\Log\LogControllerFactory;
use StepStar\Controller\Log\LogRechercheController;
use StepStar\Controller\Log\LogRechercheControllerFactory;
use StepStar\Controller\Oai\OaiConsoleController;
use StepStar\Controller\Oai\OaiConsoleControllerFactory;
use StepStar\Facade\Envoi\EnvoiFacade;
use StepStar\Facade\Envoi\EnvoiFacadeFactory;
use StepStar\Facade\Generate\GenerateFacade;
use StepStar\Facade\Generate\GenerateFacadeFactory;
use StepStar\Form\Envoi\EnvoiFichiersForm;
use StepStar\Form\Envoi\EnvoiThesesForm;
use StepStar\Form\Generate\GenerateForm;
use StepStar\Provider\StepStarPrivileges;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Api\ApiServiceFactory;
use StepStar\Service\Fetch\FetchService;
use StepStar\Service\Fetch\FetchServiceFactory;
use StepStar\Service\Log\LogService;
use StepStar\Service\Log\LogServiceFactory;
use StepStar\Service\Log\Recherche\LogSearchService;
use StepStar\Service\Log\Recherche\LogSearchServiceFactory;
use StepStar\Service\Oai\OaiService;
use StepStar\Service\Oai\OaiServiceFactory;
use StepStar\Service\Soap\SoapClient;
use StepStar\Service\Soap\SoapClientFactory;
use StepStar\Service\Tef\TefService;
use StepStar\Service\Tef\TefServiceFactory;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xml\XmlServiceFactory;
use StepStar\Service\Xsl\XslService;
use StepStar\Service\Xsl\XslServiceFactory;
use StepStar\Service\Zip\ZipService;
use StepStar\Service\Zip\ZipServiceFactory;
use Unicaen\Console\Router\Simple;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    /**
     * Options du module StepStar.
     */
    'step_star' => [
        /**
         * Options pour la génération du fichier XML intermédiaire (avant génération TEF).
         */
        'xml' => [
            // codes des types de financements correspondant à un contrat doctoral
            'codes_type_financ_contrat_doctoral' => [
                '1', // Contrat doctoral
                '2', // Contrat doctoral-mission d'enseignement
                '3', // Contrat doctoral avec autres missions
                'K', // 10-Contrat Doctoral : ministériel
                'L', // 14-Contrat Doctoral : financerment privé
                'M', // 30-Financement contrats recherche
                'Q', // 10-Contrat Doctoral : ministériel
                'R', // Contrat doctoral établissement
                'R', // Contrat Doctoral  Région 100 %
                'S', // 12-Contrat Doctoral : région co-financé
                'S', // Contrat doctoral ENSICAEN
                'T', // Contrat Doctoral  Autres organismes
                'T', // Contrat doctoral EPST
                'U', // 14-Contrat Doctoral : financerment privé
                'U', // Contrat doctoral autre organisme
                //'V', // Sans contrat doctoral
                'W', // Contrat Doctoral  Région 50%
                'W', // Contrat doctoral Région RIN 100%
                'Y', // Contrat Doctoral Etablissement
                'Y', // Contrat doctoral Région RIN 50%
            ],
            // codes des types de financements correspondant au dispositif CIFRE
            'codes_orig_financ_cifre' => [
                '31', // Conventions CIFRE
            ],
            // paramètres concernant la section "partenaires de recherche"
            'params_partenaire_recherche' => [
                'libelle' => "Établissement co-accrédité",
            ],
        ],
        /**
         * Options pour la génération des fichiers au format TEF.
         */
        'tef' => [
            // chemin du template twig permettant de générer le fichier de transformation XSL
            // (fichier XSL étant ensuite utilisé pour générer les fichiers TEF à partir du fichier XML intermédiaire)
            'xsl_template_path' => __DIR__ . '/xml2tef.xsl.twig',
            // paramètres nécessaires à la génération du fichier XSL à partir du template twig
            'xsl_template_params' => [
                // identifiant STEP/STAR de l'établissement
                'etablissementStepStar' => 'XXXX',
                // identifiant "autorité SUDOC" de l'établissement de soutenance
                'autoriteSudoc_etabSoutenance' => '123456789',
                // noms de balises
                'these' => [
                    'rootTag' => 'THESES',
                    'tag' => 'THESE',
                ],
            ],
            // préfixe des répertoires temporaires créés lors de la génération
            'output_dir_path_prefix' => '/tmp/sygal_stepstar_',
            // faut-il supprimer les répertoires/fichiers temporaires après la génération ?
            'clean_after_work' => false,
        ],
        /**
         * Options pour l'appel du web service Step-Star.
         */
        'api' => [
            'soap_client' => [
                'wsdl' => [
                    'url' => 'https://xxxx/yyyyy.wsdl',
                ],
                'soap' => [
                    'version' => SOAP_1_1, // cf. extension "php-soap"
                    //'proxy_host' => 'proxy.unicaen.fr',
                    //'proxy_port' => 3128,
                ],
            ],
            'operations' => [
//                'deposer' => 'deposer',
//                'deposer_avec_zip' => 'deposerAvecZip',
                'deposer' => 'Depot',
                'deposer_avec_zip' => 'DepotAvecZip',
            ],
            'params' => [
                // identifiant STEP/STAR de l'établissement (todo: identique à 'etablissementStepStar' ?)
                'idEtablissement' => 'XXXX',
            ],
        ],
        /**
         * Options concernant les notifications
         */
        'notification' => [
            'templates' => [
                'erreur_envoi' => __DIR__ . '/../view/step-star/notification/envois_en_erreur.phtml'
            ],
        ],
        /**
         * Options concernant la classification Dewey (sets OAI-PMH)
         */
        'oai' => [
            'sise_oai_set_file_path' => __DIR__ . '/../data/oai/siseOaiSet.xml',
            'oai_sets_file_path' => __DIR__ . '/../data/oai/oaiSets.xml', // https://www.theses.fr/schemas/tef/recommandation/oaiSets.xml
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'StepStar\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/StepStar/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'step-star' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/step-star',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'infos' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/infos',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'infos',
                            ],
                        ],
                    ],
                    'generation' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/generation',
                            'defaults' => [
                                'controller' => GenerateController::class,
                                'action' => 'generer-theses',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'telecharger-tef' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/telecharger-tef',
                                    'defaults' => [
                                        'controller' => GenerateController::class,
                                        'action' => 'telecharger-tef',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'envoi' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/envoi',
                            'defaults' => [
                                'controller' => EnvoiController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'theses' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/theses',
                                    'defaults' => [
                                        'action' => 'envoyer-theses',
                                    ],
                                ],
                            ],
                            'fichiers' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/fichiers',
                                    'defaults' => [
                                        'action' => 'envoyer-fichiers',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'log' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/log',
                            'defaults' => [
                                'controller' => LogRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'controller' => LogRechercheController::class,
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'consulter' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/consulter/:log',
                                    'defaults' => [
                                        /**
                                         * @see \StepStar\Controller\Log\LogController::consulterAction()
                                         */
                                        'controller' => LogController::class,
                                        'action' => 'consulter',
                                    ],
                                    'constraints' => [
                                        'log' => '\d+',
                                    ],
                                ],
                            ],
                            'telecharger-tef' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/telecharger-tef/:log/:hash',
                                    'defaults' => [
                                        /**
                                         * @see \StepStar\Controller\Log\LogController::telechargerTefAction()
                                         */
                                        'controller' => LogController::class,
                                        'action' => 'telecharger-tef',
                                    ],
                                    'constraints' => [
                                        'log' => '\d+',
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
                            'step-star' => [
                                'label' => 'STEP-STAR',
                                'route' => 'step-star/infos',
                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index'),
                                'icon' => 'fas fa-book',
                                'order' => 1050,
                                'pages' => [
                                    'infos' => [
                                        'label' => "Infos",
                                        'route' => 'step-star/infos',
                                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index'),
                                        'order' => 10,
                                    ],
                                    'generation' => [
                                        'label' => "Génération TEF",
                                        'route' => 'step-star/generation',
                                        'resource' => PrivilegeController::getResourceId(GenerateController::class, 'generer-theses'),
                                        'order' => 15,
                                    ],
                                    'envoi-fichiers' => [
                                        'label' => "Envoi de fichiers TEF",
                                        'route' => 'step-star/envoi/fichiers',
                                        'resource' => PrivilegeController::getResourceId(EnvoiController::class, 'envoyer-fichiers'),
                                        'order' => 20,
                                    ],
                                    'envoi-theses' => [
                                        'label' => "Envoi de thèses",
                                        'route' => 'step-star/envoi/theses',
                                        'resource' => PrivilegeController::getResourceId(EnvoiController::class, 'envoyer-theses'),
                                        'order' => 21,
                                    ],
                                    'logs' => [
                                        'label' => 'Logs',
                                        'route' => 'step-star/log',
                                        'order' => 30,
                                        'pages' => [
                                            'logs' => [
                                                'label' => "Détails d'un log",
                                                'route' => 'step-star/log/consulter',
                                                'resource' => PrivilegeController::getResourceId(LogController::class, 'consulter'),
                                                'visible' => false,
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
    'console' => [
        'router' => [
            'routes' => [
                'generateConfigFileFromSiseOaiSetXmlFile' => [
                    'type' => Simple::class,
                    'options' => [
                        'route' => 'step-star:generateConfigFileFromSiseOaiSetXmlFile [--output-dir=]',
                        'defaults' => [
                            /**
                             * @see OaiConsoleController::generateConfigFileFromSiseOaiSetXmlFileAction()
                             */
                            'controller' => OaiConsoleController::class,
                            'action' => 'generateConfigFileFromSiseOaiSetXmlFile',
                        ],
                    ],
                ],
                'generateConfigFileFromOaiSetsXmlFile' => [
                    'type' => Simple::class,
                    'options' => [
                        'route' => 'step-star:generateConfigFileFromOaiSetsXmlFile [--output-dir=]',
                        'defaults' => [
                            /**
                             * @see OaiConsoleController::generateConfigFileFromOaiSetsXmlFileAction()
                             */
                            'controller' => OaiConsoleController::class,
                            'action' => 'generateConfigFileFromOaiSetsXmlFile',
                        ],
                    ],
                ],

                'generer-theses' => [
                    'type' => Simple::class,
                    'options' => [
                        'route' => Module::STEP_STAR__CONSOLE_ROUTE__GENERER_THESES . ' [--these=] [--etat=] [--etablissement=] [--date-soutenance-null] [--date-soutenance-min=] [--date-soutenance-max=]',
                        'defaults' => [
                            /**
                             * @see GenerateConsoleController::genererThesesAction()
                             */
                            'controller' => GenerateConsoleController::class,
                            'action' => 'generer-theses',
                        ],
                    ],
                ],
                'envoyer-fichiers' => [
                    'type' => Simple::class,
                    'options' => [
                        'route' => Module::STEP_STAR__CONSOLE_ROUTE__ENVOYER_FICHIERS . ' --dir= [--tag=]',
                        'defaults' => [
                            /**
                             * @see EnvoiConsoleController::envoyerFichiersAction()
                             */
                            'controller' => EnvoiConsoleController::class,
                            'action' => 'envoyer-fichiers',
                        ],
                    ],
                ],
                'envoyer-theses' => [
                    'type' => Simple::class,
                    'options' => [
                        'route' => Module::STEP_STAR__CONSOLE_ROUTE__ENVOYER_THESES . ' [--these=] [--etat=] [--etablissement=] [--date-soutenance-null] [--date-soutenance-min=] [--date-soutenance-max=] [--tag=] [--force] [--clean]',
                        'defaults' => [
                            /**
                             * @see EnvoiConsoleController::envoyerThesesAction()
                             */
                            'controller' => EnvoiConsoleController::class,
                            'action' => 'envoyer-theses',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                ////////////////////////////// Console //////////////////////////////////////
                [
                    /**
                     * @see OaiConsoleController::generateConfigFileFromOaiSetsXmlFileAction()
                     * @see OaiConsoleController::generateConfigFileFromSiseOaiSetXmlFileAction()
                     */
                    'controller' => OaiConsoleController::class,
                    'action' => [
                        'generateConfigFileFromOaiSetsXmlFile',
                        'generateConfigFileFromSiseOaiSetXmlFile',
                    ],
                    'role' => [],
                ],
                [
                    /**
                     * @see GenerateConsoleController::genererThesesAction()
                     */
                    'controller' => GenerateConsoleController::class,
                    'action' => [
                        'generer-theses',
                    ],
                    'role' => [],
                ],
                [
                    /**
                     * @see EnvoiConsoleController::envoyerFichiersAction()
                     */
                    'controller' => EnvoiConsoleController::class,
                    'action' => [
                        'envoyer-fichiers',
                    ],
                    'role' => [],
                ],
                [
                    /**
                     * @see EnvoiConsoleController::envoyerThesesAction()
                     */
                    'controller' => EnvoiConsoleController::class,
                    'action' => [
                        'envoyer-theses',
                    ],
                    'role' => [],
                ],

                ////////////////////////////// Http //////////////////////////////////////
                [
                    /**
                     * @see \StepStar\Controller\IndexController::indexAction()
                     */
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                        'infos',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::LOG_LISTER,
                ],
                [
                    /**
                     * @see \StepStar\Controller\Generate\GenerateController::genererThesesAction()
                     * @see \StepStar\Controller\Generate\GenerateController::telechargerTefAction()
                     */
                    'controller' => GenerateController::class,
                    'action' => [
                        'generer-theses',
                        'telecharger-tef',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::LOG_LISTER,
                ],
                [
                    /**
                     * @see \StepStar\Controller\Envoi\EnvoiController::envoyerFichiersAction()
                     */
                    'controller' => EnvoiController::class,
                    'action' => [
                        'envoyer-fichiers',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::LOG_LISTER,
                ],
                [
                    /**
                     * @see \StepStar\Controller\Envoi\EnvoiController::envoyerThesesAction()
                     */
                    'controller' => EnvoiController::class,
                    'action' => [
                        'envoyer-theses',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::LOG_LISTER,
                ],
                [
                    /**
                     * @see LogController::indexAction()
                     */
                    'controller' => LogController::class,
                    'action' => [
                        'consulter',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::LOG_CONSULTER,
                ],
                [
                    /**
                     * @see LogController::telechargerTefAction()
                     */
                    'controller' => LogController::class,
                    'action' => [
                        'telecharger-tef',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::TEF_TELECHARGER,
                ],
                [
                    /**
                     * @see LogRechercheController::indexAction()
                     * @see LogRechercheController::filtersAction()
                     */
                    'controller' => LogRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'role' => [],
                    'privileges' => StepStarPrivileges::LOG_LISTER,
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            GenerateFacade::class => GenerateFacadeFactory::class,
            EnvoiFacade::class => EnvoiFacadeFactory::class,

            XmlService::class => XmlServiceFactory::class,
            XslService::class => XslServiceFactory::class,
            TefService::class => TefServiceFactory::class,
            ApiService::class => ApiServiceFactory::class,
            SoapClient::class => SoapClientFactory::class,
            ZipService::class => ZipServiceFactory::class,
            OaiService::class => OaiServiceFactory::class,

            LogService::class => LogServiceFactory::class,
            LogSearchService::class => LogSearchServiceFactory::class,

            FetchService::class => FetchServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,

            LogController::class => LogControllerFactory::class,
            LogRechercheController::class => LogRechercheControllerFactory::class,

            GenerateController::class => GenerateControllerFactory::class,
            GenerateConsoleController::class => GenerateConsoleControllerFactory::class,

            EnvoiController::class => EnvoiControllerFactory::class,
            EnvoiConsoleController::class => EnvoiConsoleControllerFactory::class,

            OaiConsoleController::class => OaiConsoleControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            GenerateForm::class,
            EnvoiFichiersForm::class,
            EnvoiThesesForm::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];