<?php

namespace These\Form\Attestation;

use Interop\Container\ContainerInterface;

class AttestationTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var \These\Form\Attestation\AttestationHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('AttestationHydrator');

        $form = new AttestationTheseForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}