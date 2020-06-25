<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\EcoleDoctoraleHydrator;
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
