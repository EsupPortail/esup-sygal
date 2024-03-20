<?php

namespace Depot\Form\Description;

use Interop\Container\ContainerInterface;

class DescriptionTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $hydrator = $container->get('HydratorManager')->get(DescriptionTheseHydrator::class);
        $form = new DescriptionTheseForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}