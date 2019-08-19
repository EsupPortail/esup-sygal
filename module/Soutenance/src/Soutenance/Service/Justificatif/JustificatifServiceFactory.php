<?php

namespace Soutenance\Service\Justificatif;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class JustificatifServiceFactory {

    public function __invoke(ServiceLocatorInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var JustificatifService $service */
        $service = new JustificatifService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}