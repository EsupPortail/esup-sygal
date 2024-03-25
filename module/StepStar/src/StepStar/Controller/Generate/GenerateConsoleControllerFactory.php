<?php

namespace StepStar\Controller\Generate;

use Psr\Container\ContainerInterface;
use StepStar\Facade\Generate\GenerateFacade;
use StepStar\Service\Fetch\FetchService;

class GenerateConsoleControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GenerateConsoleController
    {
        $controller = new GenerateConsoleController();

        /** @var \StepStar\Service\Fetch\FetchService $fetchService */
        $fetchService = $container->get(FetchService::class);
        $controller->setFetchService($fetchService);

        /** @var \StepStar\Facade\Generate\GenerateFacade $generateFacade */
        $generateFacade = $container->get(GenerateFacade::class);
        $controller->setGenerateFacade($generateFacade);

        return $controller;
    }
}