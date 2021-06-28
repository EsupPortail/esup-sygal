<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Service\Seance\SeanceService;
use Interop\Container\ContainerInterface;

class SeanceControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var SeanceService $seanceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $seanceService = $container->get(SeanceService::class);

        $controller = new SeanceController();
        $controller->setEntityManager($entityManager);
        $controller->setSeanceService($seanceService);
        return $controller;
    }
}