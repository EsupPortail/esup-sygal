<?php

namespace Soutenance\Form\Refus;

use Laminas\Form\Element\Submit;
use Laminas\Form\Form;

class RefusForm extends Form {

    public function init()
    {
        $this->add([
            'name' => 'motif',
            'type' => 'textarea',
            'options' => [
                'label' => 'Motif de refus de la proposition: ',
                'label_attributes' => [
                    'class' => 'form-label',
                ],
            ],
            'attributes' => [
                'class' => 'motif',
            ]
        ]);

        $this->add((new Submit('submit'))
            ->setValue("Refuser la proposition")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}