<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Form\Seance\SeanceForm;
use Formation\Service\Seance\SeanceService;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;

class SessionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $sessionService = $container->get(SessionService::class);

        $controller = new SessionController();

        $controller->setEntityManager($entityManager);
        //services
        $controller->setSessionService($sessionService);
        //forms

        return $controller;
    }
}