<?php

namespace Soutenance\Service\Qualite;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class QualiteServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
         /**
          * @var EntityManager $entityManager
          */
         $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var QualiteService $service */
        $service = new QualiteService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}