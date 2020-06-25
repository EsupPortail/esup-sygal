<?php

namespace Application\Form\Factory;

use Application\Form\MetadonneeTheseForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class MetadonneeTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        $form = new MetadonneeTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}