<?php

namespace Soutenance\Form\Anglais;

use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Submit;
use Zend\Form\Form;

class AnglaisForm extends Form
{

    public function init()
    {
        $this->add(
            [
                'type' => Checkbox::class,
                'name' => 'manuscrit',
                'options' => [
                    'label' => "Manuscrit rédigé en anglais",
                ],
                'attributes' => [
                    'id' => 'manuscrit',
                ],
            ]
        );
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

        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}