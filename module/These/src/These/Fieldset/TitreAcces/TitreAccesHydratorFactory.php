<?php

namespace These\Fieldset\TitreAcces;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class TitreAccesHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TitreAccesHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new TitreAccesHydrator($entityManager);
    }
}