<?php

namespace Application\Service\Profil;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProfilServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var ProfilService $service */
        $service = new ProfilService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}