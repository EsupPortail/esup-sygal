<?php

namespace These\Form\Factory;

use These\Form\RechercherCoEncadrantForm;
use Interop\Container\ContainerInterface;

class RechercherCoEncadrantFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        $form = new RechercherCoEncadrantForm();
        return $form;
    }
}