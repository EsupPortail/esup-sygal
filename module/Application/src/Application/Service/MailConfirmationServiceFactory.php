<?php

namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class MailConfirmationServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return MailConfirmationService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var MailConfirmationService $service */
        $service = new MailConfirmationService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}
