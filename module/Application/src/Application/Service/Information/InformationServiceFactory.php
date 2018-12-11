<?php

namespace Application\Service\Information;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class InformationServiceFactory {
    /**
     * @param ContainerInterface $container
     * @return InformationService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new InformationService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
