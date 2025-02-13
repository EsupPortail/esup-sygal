<?php

namespace Soutenance\Service\Validation\ValidationThese;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Validation\Service\ValidationService;

class ValidationTheseServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationTheseService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var IndividuService $individuService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $individuService = $container->get(IndividuService::class);

        $service = new ValidationTheseService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setIndividuService($individuService);

        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

        /** @var \Validation\Service\ValidationThese\ValidationTheseService $validationTheseService */
        $validationTheseService = $container->get(\Validation\Service\ValidationThese\ValidationTheseService::class);
        $service->setValidationTheseService($validationTheseService);

        return $service;
    }
}