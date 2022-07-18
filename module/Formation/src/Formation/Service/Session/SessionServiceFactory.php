<?php

namespace Formation\Service\Session;

use Doctrine\ORM\EntityManager;
use Formation\Service\Formation\FormationService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionService
    {
        /**
         * @var EntityManager $entitymanager
         * @var FormationService $formationService
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');
        $formationService = $container->get(FormationService::class);

        $service = new SessionService();
        $service->setEntityManager($entitymanager);
        $service->setFormationService($formationService);
        return $service;
    }
}