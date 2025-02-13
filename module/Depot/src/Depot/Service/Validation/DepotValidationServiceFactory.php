<?php

namespace Depot\Service\Validation;

use Application\Service\UserContextService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Validation\Service\ValidationThese\ValidationTheseService as ValidationEntityService;
use Validation\Service\ValidationService;

class DepotValidationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DepotValidationService
    {
        $service = new DepotValidationService();

        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);
        $service->setUserContextService($userContextService);

        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

        /** @var ValidationEntityService $validationService */
        $validationService = $container->get(ValidationEntityService::class);
        $service->setValidationTheseService($validationService);

        return $service;
    }
}