<?php

namespace Formation\Service\Justificatif;

use Doctrine\ORM\EntityManager;
use Formation\Service\Presence\PresenceService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class JustificatifServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return JustificatifService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : JustificatifService
    {
        /**
         * @var EntityManager $entityManager
         * @var PresenceService $presenceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $presenceService = $container->get(PresenceService::class);

        $service = new JustificatifService();
        $service->setEntityManager($entityManager);
        $service->setPresenceService($presenceService);
        return $service;
    }
}