<?php

namespace These\Form\DomaineHalSaisie;

use Interop\Container\ContainerInterface;

class DomaineHalSaisieFormFactory
{
    public function __invoke(ContainerInterface $container): DomaineHalSaisieForm
    {
        $form = new DomaineHalSaisieForm();
        return $form;
    }
}