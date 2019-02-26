<?php

namespace Soutenance\Form\SoutenanceRefus;

use Zend\Form\Element\Submit;
use Zend\Form\Form;

class SoutenanceRefusForm extends Form {

    public function init()
    {
        $this->add([
            'name' => 'motif',
            'type' => 'textarea',
            'options' => [
                'label' => 'Motif de refus de la proposition: ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'class' => 'form-control motif',
            ]
        ]);

        $this->add((new Submit('submit'))
            ->setValue("Refuser la proposition")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}