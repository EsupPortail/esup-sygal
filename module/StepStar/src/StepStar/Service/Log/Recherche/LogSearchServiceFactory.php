<?php

namespace StepStar\Service\Log\Recherche;

use Psr\Container\ContainerInterface;
use StepStar\Service\Log\LogService;
use Structure\Service\Etablissement\EtablissementService;

class LogSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LogSearchService
    {
        $service = new LogSearchService();

        /** @var \StepStar\Service\Log\LogService $logService */
        $logService = $container->get(LogService::class);
        $service->setLogService($logService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}