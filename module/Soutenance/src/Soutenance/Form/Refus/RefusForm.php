<?php

namespace Soutenance\Form\Refus;

use Application\Utils\FormUtils;
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

        FormUtils::addSaveButton($this, "Refuser la proposition");
    }
}