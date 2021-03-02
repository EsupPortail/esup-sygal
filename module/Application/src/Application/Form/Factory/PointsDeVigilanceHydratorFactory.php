<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\PointsDeVigilanceHydrator;
use Interop\Container\ContainerInterface;

class PointsDeVigilanceHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PointsDeVigilanceHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}