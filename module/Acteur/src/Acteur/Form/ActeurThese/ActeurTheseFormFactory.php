<?php

namespace Acteur\Form\ActeurThese;

use Acteur\Fieldset\ActeurThese\ActeurTheseFieldset;
use Interop\Container\ContainerInterface;

class ActeurTheseFormFactory
{
    public function __invoke(ContainerInterface $container): ActeurTheseForm
    {
        $form = new ActeurTheseForm();
        $form->setActeurFieldsetClass(ActeurTheseFieldset::class);

        return $form;
    }
}