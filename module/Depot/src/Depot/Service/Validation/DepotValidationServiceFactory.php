<?php

namespace Depot\Service\Validation;

use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService as ValidationEntityService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DepotValidationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DepotValidationService
    {
        $service = new DepotValidationService();

        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);
        $service->setUserContextService($userContextService);

        /** @var ValidationEntityService $validationService */
        $validationService = $container->get(ValidationEntityService::class);
        $service->setValidationService($validationService);

        return $service;
    }
}