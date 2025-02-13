<?php

namespace Soutenance\Service\Validation\ValidationHDR;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Validation\Service\ValidationService;

class ValidationHDRServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationHDRService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var IndividuService $individuService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $individuService = $container->get(IndividuService::class);

        $service = new ValidationHDRService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setIndividuService($individuService);

        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

        /** @var \Validation\Service\ValidationHDR\ValidationHDRService $validationHDRService */
        $validationHDRService = $container->get(\Validation\Service\ValidationHDR\ValidationHDRService::class);
        $service->setValidationHDRService($validationHDRService);

        return $service;
    }
}