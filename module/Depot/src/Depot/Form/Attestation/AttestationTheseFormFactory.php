<?php

namespace Depot\Form\Attestation;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AttestationTheseFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AttestationTheseForm
    {
        /** @var \Depot\Form\Attestation\AttestationHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('AttestationHydrator');

        $form = new AttestationTheseForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}