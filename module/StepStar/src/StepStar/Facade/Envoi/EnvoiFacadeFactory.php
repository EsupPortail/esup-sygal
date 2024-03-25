<?php

namespace StepStar\Facade\Envoi;

use Psr\Container\ContainerInterface;
use StepStar\Facade\Generate\GenerateFacade;
use StepStar\Service\Api\ApiService;
use StepStar\Service\Log\LogService;

class EnvoiFacadeFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EnvoiFacade
    {
        $facade = new EnvoiFacade();
        $facade->setCleanAfterWork(false);

        /** @var \StepStar\Facade\Generate\GenerateFacade $generateFacade */
        $generateFacade = $container->get(GenerateFacade::class);
        $facade->setGenerateFacade($generateFacade);

        /** @var \StepStar\Service\Api\ApiService $apiService */
        $apiService = $container->get(ApiService::class);
        $facade->setApiService($apiService);

        /** @var \StepStar\Service\Log\LogService $logService */
        $logService = $container->get(LogService::class);
        $facade->setLogService($logService);

        /** @var array $config */
        $config = $container->get('Config');
        $cleanAfterWork = $config['step_star']['tef']['clean_after_work'] ?? true;
        $facade->setCleanAfterWork($cleanAfterWork);

        return $facade;
    }
}