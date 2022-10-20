<?php

namespace StepStar\Service\Tef;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xslt\XsltService;

class TefServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TefService
    {
        /**
         * @var \StepStar\Service\Xslt\XsltService $xslService
         * @var \StepStar\Service\Xml\XmlService $xmlService
         */
        try {
            $xslService = $container->get(XsltService::class);
        } catch (ContainerExceptionInterface $e) {
        }
        $xmlService = $container->get(XmlService::class);

        $service = new TefService();
        $service->setXmlService($xmlService);
        $service->setXsltService($xslService);

        return $service;
    }
}