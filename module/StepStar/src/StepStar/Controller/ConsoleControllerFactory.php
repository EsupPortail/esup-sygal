<?php

namespace StepStar\Controller;

use Interop\Container\ContainerInterface;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xsl\XslService;
use StepStar\Service\Zip\ZipService;

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
         * @var XmlService $xmlService
         * @var XslService $xslService
         * @var ApiService $apiService
         * @var ZipService $zipService
         */
        $xmlService = $container->get(XmlService::class);
        $xslService = $container->get(XslService::class);
        $apiService = $container->get(ApiService::class);
        $zipService = $container->get(ZipService::class);

        $controller = new ConsoleController();
        $controller->setXmlService($xmlService);
        $controller->setXslService($xslService);
        $controller->setApiService($apiService);
        $controller->setZipService($zipService);

        return $controller;
    }
}