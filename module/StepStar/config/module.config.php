<?php

namespace StepStar;

use StepStar\Controller\ConsoleController;
use StepStar\Controller\ConsoleControllerFactory;
use StepStar\Controller\IndexController;
use StepStar\Controller\IndexControllerFactory;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Api\ApiServiceFactory;
use StepStar\Service\Soap\SoapClient;
use StepStar\Service\Soap\SoapClientFactory;
use StepStar\Service\Tef\TefService;
use StepStar\Service\Tef\TefServiceFactory;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xml\XmlServiceFactory;
use StepStar\Service\Xslt\XsltService;
use StepStar\Service\Xslt\XsltServiceFactory;
use StepStar\Service\Zip\ZipService;
use StepStar\Service\Zip\ZipServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Mvc\Console\Router\Simple;

return [
    'step_star' => [
        'tef' => [
            'xml2tef_xsl_template_path' => __DIR__ . '/xml2tef.xsl.twig',
        ],
        'api' => [
            'soap_client' => [
                'wsdl' => [
                    'url' => 'https://imports-test.theses.fr/services/DepotTEF?wsdl',
                ],
                'soap' => [
                    'version' => SOAP_1_1, // cf. extension "php-soap"
                    'proxy_host' => 'proxy.unicaen.fr',
                    'proxy_port' => 3128,
                ],
            ],
            'params' => [
//                'idEtablissement' => 'SYGA',
                'idEtablissement' => 'NORM',
//                'ws' => 'ABES_TRF_THESE',
            ],
        ],
        'step' => [

        ],
        'star' => [

        ],
    ],
    'router' => [
        'routes' => [
            'application' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/StepStar',
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
                        'route'    => 'step-star generer-xml --these= --to= [--verbose]',
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
                        'route'    => 'step-star generer-tef --from= [--dir=] [--verbose]',
                        'defaults' => [
                            /**
                             * @see ConsoleController::genererTefAction()
                             */
                            'controller' => ConsoleController::class,
                            'action'     => 'generer-tef',
                        ],
                    ],
                ],
                'deposer' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'step-star deposer --tef= [--zip=] [--verbose]',
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
//                    'privileges' => \These\Provider\Privilege\ThesePrivileges::THESE_RECHERCHE,
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
            XmlService::class => XmlServiceFactory::class,
            XsltService::class => XsltServiceFactory::class,
            TefService::class => TefServiceFactory::class,
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