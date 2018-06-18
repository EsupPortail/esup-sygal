<?php

namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class MailConfirmationServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MailConfirmationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var MailConfirmationService $service */
        $service = new MailConfirmationService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}
