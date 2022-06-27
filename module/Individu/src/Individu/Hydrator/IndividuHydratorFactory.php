<?php

namespace Individu\Hydrator;

use Psr\Container\ContainerInterface;

class IndividuHydratorFactory
{
    public function __invoke(ContainerInterface $container): IndividuHydrator
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        return new IndividuHydrator($em);
    }
}