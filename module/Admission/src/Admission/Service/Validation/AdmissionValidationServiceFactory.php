<?php

namespace Admission\Service\Validation;

use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionValidationServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionValidationService
    {
        /**
         * @var UserContextService $userContextService;
         * @var IndividuService $individuService
         */
        $userContextService = $container->get(UserContextService::class);

        $service = new AdmissionValidationService();
        $service->setUserContextService($userContextService);
        $service->setEventManager($container->get('EventManager'));

        return $service;
    }
}