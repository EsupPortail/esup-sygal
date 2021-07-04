<?php

namespace StepStar\Service\Xml;

use Interop\Container\ContainerInterface;
use XMLWriter;
use Zend\ServiceManager\Factory\FactoryInterface;

class XmlServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new XmlService();
        $service->setWriter(new XMLWriter());

        return $service;
    }
}