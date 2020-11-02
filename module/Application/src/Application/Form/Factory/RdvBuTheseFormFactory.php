<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RdvBuHydrator;
use Application\Form\RdvBuTheseForm;
use Interop\Container\ContainerInterface;

class RdvBuTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var RdvBuHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('RdvBuHydrator');

        $form = new RdvBuTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}