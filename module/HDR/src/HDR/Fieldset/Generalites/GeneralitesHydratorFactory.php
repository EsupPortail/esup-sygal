<?php

namespace HDR\Fieldset\Generalites;

use Application\Service\Source\SourceService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GeneralitesHydratorFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GeneralitesHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        $hydrator = new GeneralitesHydrator($entityManager);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $hydrator->setSourceService($sourceService);

        return $hydrator;
    }
}