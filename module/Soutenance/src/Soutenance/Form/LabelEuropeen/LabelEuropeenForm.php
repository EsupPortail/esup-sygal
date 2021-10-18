<?php

namespace Soutenance\Form\LabelEuropeen;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;

class LabelEuropeenForm extends Form {

    public function init()
    {
        $this->add(
            [
                'type' => Checkbox::class,
                'name' => 'label',
                'options' => [
                    'label' => "Demande de label européen",
                ],
                'attributes' => [
                    'id' => 'label',
                ],
            ]
        );
        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}