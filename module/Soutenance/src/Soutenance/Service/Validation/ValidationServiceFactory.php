<?php

namespace Soutenance\Service\Validation;


use Application\Service\Individu\IndividuService;
use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class ValidationServiceFactory
{

    /**
     * @param ContainerInterface $container
     * @return ValidationService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var IndividuService $individuService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $individuService = $container->get('IndividuService');

        /** @var ValidationService $service */
        $service = new ValidationService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setIndividuService($individuService);
        return $service;
    }
}