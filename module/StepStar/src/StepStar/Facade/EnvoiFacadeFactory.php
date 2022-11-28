<?php

namespace StepStar\Facade;

use Psr\Container\ContainerInterface;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Log\LogService;
use StepStar\Service\Tef\TefService;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xsl\XslService;

class EnvoiFacadeFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EnvoiFacade
    {
        $facade = new EnvoiFacade();

        /**
         * @var \StepStar\Service\Xml\XmlService $xmlService
         */
        $xmlService = $container->get(XmlService::class);
        $facade->setXmlService($xmlService);

        /**
         * @var \StepStar\Service\Xsl\XslService $xslService
         */
        $xslService = $container->get(XslService::class);
        $facade->setXslService($xslService);

        /**
         * @var \StepStar\Service\Tef\TefService $tefService
         */
        $tefService = $container->get(TefService::class);
        $facade->setTefService($tefService);

        /**
         * @var \StepStar\Service\Api\ApiService $apiService
         */
        $apiService = $container->get(ApiService::class);
        $facade->setApiService($apiService);

        /**
         * @var \StepStar\Service\Log\LogService $logService
         */
        $logService = $container->get(LogService::class);
        $facade->setLogService($logService);

        return $facade;
    }
}