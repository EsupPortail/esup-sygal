<?php

namespace Soutenance\Form\DateLieu;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\DateTime;
use Laminas\Form\Element\Time;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class DateLieuForm extends Form {

    public function init()
    {

        $this->add([
            'name' => 'date',
            'type' => DateTime::class,
            'options' => [
                'label' => 'Date de la soutenance : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'format' => 'd/m/Y',
            ],
            'attributes' => [
                'class' => 'form-control',

                //'min'  => $twomonth->format('Y-m-d'),
            ]
        ]);

        $this->add([
            'name' => 'heure',
            'type' => Time::class,
            'options' => [
                'label' => 'Heure de la soutenance : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
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
            'type' => Radio::class,
            'options' => [
                'label' => 'La soutenance aura lieu :',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'value_options' => [
                    '0' => 'dans l\'établissement d\'encadrement',
                    '1' => 'hors l\'établissement d\'encadrement',
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

        $this->setInputFilter((new Factory())->createInputFilter([
            'date' => [ 'required' => true, ],
            'heure' => [ 'required' => true, ],
            'lieu' => [ 'required' => true, ],
            'exterieur' => [ 'required' => true, ],
        ]));
    }
}