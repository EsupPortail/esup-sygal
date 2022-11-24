<?php

namespace Application\Form\Factory;

use Depot\Form\PointsDeVigilanceForm;
use Interop\Container\ContainerInterface;

class PointsDeVigilanceFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new PointsDeVigilanceForm();

        $hydrator = $container->get('HydratorManager')->get('PointsDeVigilanceHydrator');
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}