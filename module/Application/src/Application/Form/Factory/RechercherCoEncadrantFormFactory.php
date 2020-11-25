<?php

namespace Application\Form\Factory;

use Application\Form\RechercherCoEncadrantForm;
use Interop\Container\ContainerInterface;

class RechercherCoEncadrantFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        $form = new RechercherCoEncadrantForm();
        return $form;
    }
}