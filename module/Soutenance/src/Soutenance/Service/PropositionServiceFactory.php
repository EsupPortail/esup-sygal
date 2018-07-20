<?php

namespace  Soutenance\Service;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class PropositionServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');

        /** @var PropositionService $service */
        $service = new PropositionService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
