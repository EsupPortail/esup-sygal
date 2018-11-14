<?php

namespace Application\Service\Financement;

use Application\Entity\Db\OrigineFinancement;
use Doctrine\ORM\EntityManager;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class FinancementServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {

        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var FinancementService $service */
        $service = new FinancementService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}