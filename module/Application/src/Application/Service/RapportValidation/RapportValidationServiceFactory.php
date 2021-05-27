<?php

namespace Application\Service\RapportValidation;

use Application\Service\Individu\IndividuService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;

class RapportValidationServiceFactory
{
    public function __invoke(ContainerInterface $container): RapportValidationService
    {
        $individuService = $container->get(IndividuService::class);
        $userContextService = $container->get(UserContextService::class);

        $service = new RapportValidationService();
        $service->setIndividuService($individuService);
        $service->setUserContextService($userContextService);

        return $service;
    }
}