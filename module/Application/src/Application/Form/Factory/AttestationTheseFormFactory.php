<?php

namespace Application\Form\Factory;

use Application\Form\AttestationTheseForm;
use Application\Form\Hydrator\AttestationHydrator;
use Interop\Container\ContainerInterface;

class AttestationTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var AttestationHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('AttestationHydrator');

        $form = new AttestationTheseForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}