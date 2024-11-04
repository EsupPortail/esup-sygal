<?php

namespace These\Form\Encadrement;

use Interop\Container\ContainerInterface;
use Laminas\Hydrator\ReflectionHydrator;
use These\Entity\Db\These;

class EncadrementFormFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EncadrementForm
    {
        $form = new EncadrementForm('Encadrement');

        $form->setHydrator(new ReflectionHydrator());
        $form->setObject(new These());

        return $form;
    }
}