<?php

namespace Acteur\Form\ActeurHDR;

use Acteur\Fieldset\ActeurHDR\ActeurHDRFieldset;
use Interop\Container\ContainerInterface;

class ActeurHDRFormFactory
{
    public function __invoke(ContainerInterface $container): ActeurHDRForm
    {
        $form = new ActeurHDRForm();
        $form->setActeurFieldsetClass(ActeurHDRFieldset::class);

        return $form;
    }
}