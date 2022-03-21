<?php

namespace StepStar\Controller\Oai;

use Psr\Container\ContainerInterface;
use StepStar\Service\Oai\OaiService;

class OaiConsoleControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): OaiConsoleController
    {
        /**
         * @var \StepStar\Service\Oai\OaiService $oaiService
         */
        $oaiService = $container->get(OaiService::class);

        $controller = new OaiConsoleController();
        $controller->setOaiSetService($oaiService);

        return $controller;
    }
}