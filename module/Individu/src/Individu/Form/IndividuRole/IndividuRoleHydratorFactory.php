<?php

namespace Individu\Form\IndividuRole;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class IndividuRoleHydratorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuRoleHydrator
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        return new IndividuRoleHydrator($entityManager);
    }
}