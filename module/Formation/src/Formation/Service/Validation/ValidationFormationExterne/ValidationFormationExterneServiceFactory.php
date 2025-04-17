<?php

namespace Formation\Service\Validation\ValidationFormationExterne;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Validation\Service\ValidationService;

class ValidationFormationExterneServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationFormationExterneService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var IndividuService $individuService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $individuService = $container->get(IndividuService::class);

        $service = new ValidationFormationExterneService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setIndividuService($individuService);

        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

        /** @var \Validation\Service\ValidationFormationExterne\ValidationFormationExterneService $validationFormationExterneService */
        $validationFormationExterneService = $container->get(\Validation\Service\ValidationFormationExterne\ValidationFormationExterneService::class);
        $service->setValidationFormationExterneService($validationFormationExterneService);

        return $service;
    }
}