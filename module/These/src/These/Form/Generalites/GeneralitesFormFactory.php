<?php

namespace These\Form\Generalites;

use Interop\Container\ContainerInterface;

class GeneralitesFormFactory
{
    public function __invoke(ContainerInterface $container): GeneralitesForm
    {
        return new GeneralitesForm();
    }
}