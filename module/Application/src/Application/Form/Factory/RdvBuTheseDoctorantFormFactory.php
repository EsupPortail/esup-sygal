<?php

namespace Application\Form\Factory;

use Application\Form\RdvBuTheseDoctorantForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class RdvBuTheseDoctorantFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        $form = new RdvBuTheseDoctorantForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}