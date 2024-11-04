<?php

namespace These\Fieldset\Financement;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class FinancementHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new FinancementHydrator($entityManager);
    }
}