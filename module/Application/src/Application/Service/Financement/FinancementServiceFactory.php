<?php

namespace Application\Service\Financement;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class FinancementServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var FinancementService $service */
        $service = new FinancementService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}