<?php

namespace Soutenance\Form\DateLieu;

use UnicaenApp\Form\Element\Date;
use Zend\Form\Element\Button;
use Zend\Form\Element\Checkbox;
use DateTime;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Time;
use Zend\Form\Form;

class DateLieuForm extends Form {

    public function init()
    {
        $today = new DateTime();

        $this->add([
            'name' => 'date',
            'type' => Date::class,
            'options' => [
                'label' => 'Date de la soutenance : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'min'  => $today->format('Y-m-d'),
            ]
        ]);

        $this->add([
            'name' => 'heure',
            'type' => DateTime::class,
            'options' => [
                'label' => 'Heure de la soutenance : ',
//                'label_attributes' => [
//                    'class' => 'control-label',
//                ],
                'format' => 'H:i',
            ],
        ]);

        $this->add([
            'name' => 'lieu',
            'type' => Text::class,
            'options' => [
                'label' => 'Lieu de la soutenance : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'exterieur',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Thèse soutenue à l\'extérieur de l\'établissement d\'encadrement',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);
        // button
        $this->add([
            'type' => Button::class,
            'name' => 'submit',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

//        $this->setInputFilter(
//            $this->getInputFilter()
//        );
    }

//    public function getInputFilterSpecification()
//    {
//        return [
//            'date' => [
//                'name' => 'date',
//                'required' => false,
//            ],
//            'heure' => [
//                'name' => 'heure',
//                'required' => false,
//            ],
//            'lieu' => [
//                'name' => 'lieu',
//                'required' => false,
//            ],
//            'exterieur' => [
//                'name' => 'exterieur',
//                'required' => false,
//            ],
//        ];
//    }
}