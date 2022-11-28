<?php

namespace StepStar\Controller\Log;

use Psr\Container\ContainerInterface;
use StepStar\Service\Log\Recherche\LogSearchService;

class LogRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LogRechercheController
    {
        $controller = new LogRechercheController();

        /** @var \StepStar\Service\Log\Recherche\LogSearchService $logSearchService */
        $logSearchService = $container->get(LogSearchService::class);
        $controller->setSearchService($logSearchService);

        return $controller;
    }
}