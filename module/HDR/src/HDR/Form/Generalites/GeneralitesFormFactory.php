<?php

namespace HDR\Form\Generalites;

use Interop\Container\ContainerInterface;

class GeneralitesFormFactory
{
    public function __invoke(ContainerInterface $container): GeneralitesForm
    {
        return new GeneralitesForm();
    }
}