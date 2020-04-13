<?php

namespace Information\Form;

use Interop\Container\ContainerInterface;

class InformationFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var InformationHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(InformationHydrator::class);

        $form = new InformationForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}