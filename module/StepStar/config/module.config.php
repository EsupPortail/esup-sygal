<?php

namespace StepStar;

use StepStar\Controller\ConsoleController;
use StepStar\Controller\ConsoleControllerFactory;
use StepStar\Controller\IndexController;
use StepStar\Controller\IndexControllerFactory;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Api\ApiServiceFactory;
use StepStar\Config\ModuleConfig;
use StepStar\Config\ModuleConfigFactory;
use StepStar\Service\Soap\SoapClient;
use StepStar\Service\Soap\SoapClientFactory;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xml\XmlServiceFactory;
use StepStar\Service\Xsl\XslService;
use StepStar\Service\Xsl\XslServiceFactory;
use StepStar\Service\Zip\ZipService;
use StepStar\Service\Zip\ZipServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Console\Router\Simple;

return [
    /**
     * Config du module StepStar.
     */
    'step_star' => [
        // Options concernant la transformation XSLT en fichiers XML TEF.
        'xsl' => [
            // Chemin vers le template Twig permettant de générer le fichier XSL :
            'xsl_twig_template_path' => __DIR__ . '/xml2tef.xsl.twig',
            // Paramètres à injecter dans le template Twig :
            'xsl_twig_template_params' => [
                //'etablissement' => 'XXXX',
                //'autoriteSudoc_etabSoutenance' => '123456789',
            ],
            // Chemin vers le fichier XSL à générer :
            'xsl_file_path' => '/tmp/xml2tef.xsl',
        ],
        // Options concernant l'appel du web service DepotTEF de l'ABES.
        'api' => [
            // Paramètres à transmettre lors de l'appel au web service :
            'params' => [
                //'idEtablissement' => 'XXXX';
            ],
        ],
        // Options du client SOAP utilisé pour appeler le web service.
        'soap_client' => [
            // URL du WSDL fourni par le web service :
            'wsdl' => 'https://.../DepotTEF?wsdl',
            // Autres options du client SOAP :
            'soap' => [
                'version' => SOAP_1_1,
                'cache_wsdl' => 0,
                //'proxy_host' => 'host.domain.fr',
                //'proxy_port' => 3128,
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'application' => [
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

                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [

                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'generer-xml' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'step-star generer-xml --these= --to= [--anonymize]',
                        'defaults' => [
                            /**
                             * @see ConsoleController::genererXmlAction()
                             */
                            'controller' => ConsoleController::class,
                            'action'     => 'generer-xml',
                        ],
                    ],
                ],
                'generer-tef' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'step-star generer-tef --from= [--dir=]',
                        'defaults' => [
                            /**
                             * @see ConsoleController::genererTefAction()
                             */
                            'controller' => ConsoleController::class,
                            'action'     => 'generer-tef',
                        ],
                    ],
                ],
                'generer-zip' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'step-star generer-zip --these=',
                        'defaults' => [
                            /**
                             * @see ConsoleController::genererZipAction()
                             */
                            'controller' => ConsoleController::class,
                            'action'     => 'generer-zip',
                        ],
                    ],
                ],
                'deposer' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'step-star deposer --tef= [--zip=]',
                        'defaults' => [
                            /**
                             * @see ConsoleController::deposerAction()
                             */
                            'controller' => ConsoleController::class,
                            'action'     => 'deposer',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    /**
                     * @see ConsoleController::genererXmlAction()
                     * @see ConsoleController::genererTefAction()
                     * @see ConsoleController::deposerAction()
                     */
                    'controller' => ConsoleController::class,
                    'action' => [
                        'generer-xml',
                        'generer-tef',
                        'generer-zip',
                        'deposer',
                    ],
                    'role' => [],
                ],
                [
                    /**
                     * @see IndexController::indexAction()
                     */
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
//                    'privileges' => \Application\Provider\Privilege\ThesePrivileges::THESE_RECHERCHE,
                ],
                [
                    'controller' => 'DoctrineModule\Controller\Cli',
                    'roles' => [],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            ModuleConfig::class => ModuleConfigFactory::class,
            XmlService::class => XmlServiceFactory::class,
            XslService::class => XslServiceFactory::class,
            ApiService::class => ApiServiceFactory::class,
            SoapClient::class => SoapClientFactory::class,
            ZipService::class => ZipServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
            ConsoleController::class => ConsoleControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];