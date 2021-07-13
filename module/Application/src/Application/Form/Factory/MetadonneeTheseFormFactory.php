<?php

namespace Application\Form\Factory;

use Application\Form\MetadonneeTheseForm;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class MetadonneeTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);

        $form = new MetadonneeTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}