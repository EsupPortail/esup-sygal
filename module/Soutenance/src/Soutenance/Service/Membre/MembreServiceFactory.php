<?php

namespace  Soutenance\Service\Membre;

use Doctrine\ORM\EntityManager;
use Soutenance\Service\Qualite\QualiteService;
use Zend\ServiceManager\ServiceLocatorInterface;

class MembreServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var QualiteService $qualiteService
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');
        $qualiteService = $servicelocator->get(QualiteService::class);

        /** @var MembreService $service */
        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setQualiteService($qualiteService);

        return $service;
    }
}
