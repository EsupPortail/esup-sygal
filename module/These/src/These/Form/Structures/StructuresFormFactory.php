<?php

namespace These\Form\Structures;

use Interop\Container\ContainerInterface;

class StructuresFormFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructuresForm
    {
        $form = new StructuresForm('Structures');

        return $form;
    }
}