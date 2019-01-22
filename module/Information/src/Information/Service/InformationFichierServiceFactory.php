<?php

namespace Information\Service;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class InformationFichierServiceFactory {
    /**
     * @param ContainerInterface $container
     * @return InformationFichierService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new InformationFichierService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
