<?php

namespace These\Form\Factory;

use These\Form\MetadonneeTheseForm;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class MetadonneeTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get('Doctrine\Laminas\Hydrator\DoctrineObject');

        $form = new MetadonneeTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}