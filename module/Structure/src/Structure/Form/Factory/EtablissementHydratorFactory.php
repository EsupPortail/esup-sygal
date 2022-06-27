<?php

namespace Structure\Form\Factory;

use Structure\Form\Hydrator\EtablissementHydrator;
use Interop\Container\ContainerInterface;

class EtablissementHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container)
    {
        return new EtablissementHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}
