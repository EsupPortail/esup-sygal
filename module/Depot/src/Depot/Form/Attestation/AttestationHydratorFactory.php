<?php

namespace Depot\Form\Attestation;

use Interop\Container\ContainerInterface;

class AttestationHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AttestationHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}
