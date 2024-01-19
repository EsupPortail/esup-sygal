<?php

namespace These\Form\Acteur;

use Laminas\Form\Element\Button;
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

        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        $this->add(new Csrf('csrf'));
    }
}