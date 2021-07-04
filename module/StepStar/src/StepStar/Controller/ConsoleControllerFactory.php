<?php

namespace StepStar\Controller;

use Interop\Container\ContainerInterface;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Tef\TefService;
use StepStar\Service\Xml\XmlService;

class ConsoleControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return ConsoleController
     */
    public function __invoke(ContainerInterface $container): ConsoleController
    {
        /**
         * @var \StepStar\Service\Xml\XmlService $xmlService
         * @var TefService $tefService
         * @var ApiService $apiService
         */
        $xmlService = $container->get(XmlService::class);
        $tefService = $container->get(TefService::class);
        $apiService = $container->get(ApiService::class);

        $controller = new ConsoleController();
        $controller->setXmlService($xmlService);
        $controller->setTefService($tefService);
        $controller->setApiService($apiService);

        return $controller;
    }
}