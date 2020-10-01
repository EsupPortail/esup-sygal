<?php

namespace Indicateur\Form;

use Interop\Container\ContainerInterface;

class IndicateurFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new IndicateurForm();
        $form->init();

        $hydrator = $container->get('HydratorManager')->get(IndicateurHydrator::class);
        $form->setHydrator($hydrator);

        return $form;
    }

}