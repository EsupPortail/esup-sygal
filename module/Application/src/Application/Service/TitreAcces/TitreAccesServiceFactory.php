<?php

namespace Application\Service\TitreAcces;

use Application\Service\Source\SourceService;
use Psr\Container\ContainerInterface;

class TitreAccesServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        $service = new TitreAccesService();
        $service->setEntityManager($em);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        return $service;
    }
}