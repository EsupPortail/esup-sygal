<?php

namespace Soutenance\Form\Anglais;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Form;

class AnglaisForm extends Form
{

    public function init()
    {
//        $this->add(
//            [
//                'type' => Checkbox::class,
//                'name' => 'manuscrit',
//                'options' => [
//                    'label' => "Manuscrit rédigé en anglais",
//                ],
//                'attributes' => [
//                    'id' => 'manuscrit',
//                ],
//            ]
//        );
        $this->add(
            [
                'type' => Checkbox::class,
                'name' => 'soutenance',
                'options' => [
                    'label' => "Soutenance défendue en anglais",
                ],
                'attributes' => [
                    'id' => 'soutenance',
                ],
            ]
        );

        FormUtils::addSaveButton($this);
    }
}