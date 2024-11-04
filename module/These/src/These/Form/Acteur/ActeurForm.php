<?php

namespace These\Form\Acteur;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use These\Fieldset\Acteur\ActeurFieldset;

class ActeurForm extends Form
{
    public function init(): void
    {
        /** @var \These\Fieldset\Acteur\ActeurFieldset $acteurFieldset */
        $acteurFieldset = $this->getFormFactory()->getFormElementManager()->get(ActeurFieldset::class);
        $acteurFieldset->setUseAsBaseFieldset(true);
        $this->add($acteurFieldset);

        FormUtils::addSaveButton($this);

        $this->add(new Csrf('csrf'));
    }
}