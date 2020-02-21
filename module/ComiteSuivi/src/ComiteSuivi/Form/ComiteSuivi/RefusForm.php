<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use Zend\Form\Element\Submit;
use Zend\Form\Form;

class RefusForm extends Form {

    public function init()
    {
        $this->add([
            'name' => 'motif',
            'type' => 'textarea',
            'options' => [
                'label' => 'Motif de refus du comité de suivi de thèse: ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'class' => 'form-control motif',
            ]
        ]);

        $this->add((new Submit('submit'))
            ->setValue("Refuser le comité")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}
