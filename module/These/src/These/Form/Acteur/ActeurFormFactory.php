<?php

namespace These\Form\Acteur;

use Interop\Container\ContainerInterface;

class ActeurFormFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurForm
    {
        $form = new ActeurForm();

        return $form;
    }
}