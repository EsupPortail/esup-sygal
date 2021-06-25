<?php

namespace Formation\Service\Formation;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class FormationServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new FormationService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}