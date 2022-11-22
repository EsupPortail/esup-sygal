<?php

namespace Formation\Service\Session;

use Doctrine\ORM\EntityManager;
use Formation\Service\Formation\FormationService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenAuth\Service\UserContext;

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
         * @var UserContext $userService
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');
        $formationService = $container->get(FormationService::class);
        $userService = $container->get(UserContext::class);

        $service = new SessionService();
        $service->setEntityManager($entitymanager);
        $service->setFormationService($formationService);
        $service->setUserContextService($userService);
        return $service;
    }
}