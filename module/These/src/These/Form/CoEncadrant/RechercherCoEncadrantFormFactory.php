<?php

namespace These\Form\CoEncadrant;

use Interop\Container\ContainerInterface;

class RechercherCoEncadrantFormFactory
{

    public function __invoke(ContainerInterface $container): RechercherCoEncadrantForm
    {
        $form = new RechercherCoEncadrantForm();
        return $form;
    }
}