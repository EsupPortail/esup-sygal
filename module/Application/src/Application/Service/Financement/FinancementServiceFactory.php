<?php

namespace Application\Service\Financement;

use Application\Service\Source\SourceService;
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

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        return $service;
    }
}