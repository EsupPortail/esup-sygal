<?php

namespace  Soutenance\Service\Parametre;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ParametreServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');

        /** @var ParametreService $service */
        $service = new ParametreService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
