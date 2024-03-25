<?php

namespace StepStar\Controller\Envoi;

use Psr\Container\ContainerInterface;
use StepStar\Facade\Envoi\EnvoiFacade;
use StepStar\Service\Fetch\FetchService;

class EnvoiConsoleControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EnvoiConsoleController
    {
        $controller = new EnvoiConsoleController();

        /**
         * @var \StepStar\Facade\Envoi\EnvoiFacade $envoiFacade
         */
        $envoiFacade = $container->get(EnvoiFacade::class);
        $controller->setEnvoiFacade($envoiFacade);

        /**
         * @var \StepStar\Service\Fetch\FetchService $fetchService
         */
        $fetchService = $container->get(FetchService::class);
        $controller->setFetchService($fetchService);

        return $controller;
    }
}