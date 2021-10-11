<?php

namespace Soutenance\Form\AdresseSoutenance;

use Zend\Form\Element\Submit;
use Zend\Form\Form;

class AdresseSoutenanceForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'adresse',
            'type' => 'textarea',
            'options' => [
                'label' => 'Adresse exacte de la soutenance : ',
                'label_attributes' => [
                    'class' => 'form-label',
                ],
            ],
            'attributes' => [
                'class' => 'motif',
            ]
        ]);

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}