<?php

namespace Soutenance\Service\Simulation;

use Application\Service\Acteur\ActeurService;
use Application\Service\Individu\IndividuService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class SimulationServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var ActeurService $acteurService
         * @var IndividuService $individuService
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $acteurService = $serviceLocator->get(ActeurService::class);
        $individuService = $serviceLocator->get('IndividuService');

        /** @var SimulationService $service */
        $service = new SimulationService();
        $service->setEntityManager($entityManager);
        $service->setActeurService($acteurService);
        $service->setIndividuService($individuService);
        return $service;
    }
}