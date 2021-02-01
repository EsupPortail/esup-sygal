<?php

namespace Import\Service\Factory;

use Doctrine\ORM\EntityManager;
use Import\Service\SynchroService;
use Interop\Container\ContainerInterface;

class SynchroServiceFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var \UnicaenDbImport\Service\SynchroService $synchroService */
        $synchroService = $container->get(\UnicaenDbImport\Service\SynchroService::class);

        $service = new SynchroService();
        $service->setEntityManager($entityManager);
        $service->setSynchroService($synchroService);

        return $service;
    }
}