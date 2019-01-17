<?php

namespace  Soutenance\Service\Membre;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class MembreServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');

        /** @var MembreService $service */
        $service = new MembreService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
