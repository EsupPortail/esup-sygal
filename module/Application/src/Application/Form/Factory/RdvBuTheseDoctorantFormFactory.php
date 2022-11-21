<?php

namespace Application\Form\Factory;

use Depot\Form\RdvBuTheseDoctorantForm;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class RdvBuTheseDoctorantFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);

        $form = new RdvBuTheseDoctorantForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}