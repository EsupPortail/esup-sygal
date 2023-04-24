<?php

namespace RapportActivite\Service\Validation;

use Application\Service\UserContextService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;

class RapportActiviteValidationServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationService
    {
        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);

        $service = new RapportActiviteValidationService();
        $service->setIndividuService($individuService);
        $service->setUserContextService($userContextService);
        $service->setEventManager($container->get('EventManager'));

        return $service;
    }
}