<?php

namespace UnicaenAvis\Form;

use Psr\Container\ContainerInterface;

class AvisTypeFormFactory
{
    public function __invoke(ContainerInterface $container): AvisTypeForm
    {
        return new AvisTypeForm();
    }
}