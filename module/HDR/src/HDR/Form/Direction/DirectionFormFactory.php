<?php

namespace HDR\Form\Direction;

use Interop\Container\ContainerInterface;
use Laminas\Hydrator\ReflectionHydrator;
use These\Entity\Db\These;

class DirectionFormFactory
{
    public function __invoke(ContainerInterface $container): DirectionForm
    {
        $form = new DirectionForm('Direction');

        $form->setHydrator(new ReflectionHydrator());
        $form->setObject(new These());

        return $form;
    }
}