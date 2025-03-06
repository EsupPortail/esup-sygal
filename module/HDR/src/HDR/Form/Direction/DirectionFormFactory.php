<?php

namespace HDR\Form\Direction;

use HDR\Entity\Db\HDR;
use Interop\Container\ContainerInterface;
use Laminas\Hydrator\ReflectionHydrator;

class DirectionFormFactory
{
    public function __invoke(ContainerInterface $container): DirectionForm
    {
        $form = new DirectionForm('Direction');

        $form->setHydrator(new ReflectionHydrator());
        $form->setObject(new HDR());

        return $form;
    }
}