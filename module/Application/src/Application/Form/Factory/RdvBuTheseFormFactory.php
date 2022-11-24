<?php

namespace Application\Form\Factory;

use Depot\Form\Hydrator\RdvBuHydrator;
use Depot\Form\RdvBuTheseForm;
use Interop\Container\ContainerInterface;

class RdvBuTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var \Depot\Form\Hydrator\RdvBuHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('RdvBuHydrator');

        $form = new RdvBuTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}