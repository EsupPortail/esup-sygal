<?php

namespace  Soutenance\Service\Avis;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class AvisServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');

        /** @var AvisService $service */
        $service = new AvisService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
