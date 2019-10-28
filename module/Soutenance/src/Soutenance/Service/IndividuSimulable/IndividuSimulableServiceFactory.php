<?php

namespace Soutenance\Service\IndividuSimulable;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndividuSimulableServiceFactory {

    public function __invoke(ServiceLocatorInterface $manager)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $manager->get('doctrine.entitymanager.orm_default');

        /** @var IndividuSimulableService $service */
        $service = new IndividuSimulableService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}