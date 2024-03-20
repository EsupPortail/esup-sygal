<?php

namespace These\Form\DomaineHalSaisie;

use Laminas\Form\Element\Button;
use Laminas\Form\Form;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldset;

class DomaineHalSaisieForm extends Form
{
    public function init(): void
    {
         /** @var DomaineHalFieldset $domaineHalFieldset */
        $domaineHalFieldset = $this->getFormFactory()->getFormElementManager()->get(DomaineHalFieldset::class);
        $domaineHalFieldset->setUseAsBaseFieldset(true);
        $this->add($domaineHalFieldset, ['name' => 'domaineHalFieldset']);

        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer / Save',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}