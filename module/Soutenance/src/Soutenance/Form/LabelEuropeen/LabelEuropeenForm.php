<?php

namespace Soutenance\Form\LabelEuropeen;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Checkbox;
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
        FormUtils::addSaveButton($this);
    }
}