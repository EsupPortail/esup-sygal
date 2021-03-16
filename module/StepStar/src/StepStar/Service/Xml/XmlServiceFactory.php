<?php

namespace StepStar\Service\Xml;

use Application\Service\These\TheseService;
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
        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get(TheseService::class);

        $service = new XmlService();
        $service->setWriter(new XMLWriter());
        $service->setTheseService($theseService);

        return $service;
    }
}