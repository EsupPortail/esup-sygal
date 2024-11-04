<?php

namespace These\Form\Financement;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class FinancementsFormFactory
{
    public function __invoke(ContainerInterface $container): FinancementsForm
    {
        $form= new FinancementsForm();
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);
        $form->setHydrator($hydrator);
        return $form;
    }
}