<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Presence\PresenceService;
use Formation\Service\Seance\SeanceService;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PresenceControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return PresenceController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : PresenceController
    {
        /**
         * @var EntityManager $entityManager
         * @var InscriptionService $inscriptionService
         * @var PresenceService $presenceService
         * @var SeanceService $seanceService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $inscriptionService = $container->get(InscriptionService::class);
        $presenceService = $container->get(PresenceService::class);
        $seanceService = $container->get(SeanceService::class);
        $sessionService = $container->get(SessionService::class);

        $controller = new PresenceController();
        $controller->setEntityManager($entityManager);
        $controller->setInscriptionService($inscriptionService);
        $controller->setPresenceService($presenceService);
        $controller->setSeanceService($seanceService);
        $controller->setSessionService($sessionService);
        return $controller;
    }
}