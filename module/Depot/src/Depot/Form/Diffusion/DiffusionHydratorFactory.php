<?php

namespace Depot\Form\Diffusion;

use Interop\Container\ContainerInterface;

class DiffusionHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DiffusionHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}
