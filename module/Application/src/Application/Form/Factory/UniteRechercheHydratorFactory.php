<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\UniteRechercheHydrator;
use Interop\Container\ContainerInterface;

class UniteRechercheHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UniteRechercheHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}
