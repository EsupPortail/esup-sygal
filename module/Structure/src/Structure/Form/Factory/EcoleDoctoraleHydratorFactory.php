<?php

namespace Structure\Form\Factory;

use Structure\Form\Hydrator\EcoleDoctoraleHydrator;
use Interop\Container\ContainerInterface;

class EcoleDoctoraleHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container)
    {
        return new EcoleDoctoraleHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}
