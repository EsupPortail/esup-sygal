<?php

namespace Soutenance\Form\DateLieu;

use DateTime;
use Soutenance\Validator\DateGreaterThan;
use UnicaenApp\Form\Element\Date;
use Zend\Form\Element\Button;
use Zend\Form\Element\DateTime as DateTimeElement;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class DateLieuForm extends Form {

    public function init()
    {
        $twomonth = new DateTime("+2 months");

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
                'min'  => $twomonth->format('Y-m-d'),
            ]
        ]);

        $this->add([
            'name' => 'heure',
            'type' => DateTimeElement::class,
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
            'date' => [
                'name' => 'date',
                'required' => true,
                'validators' => [
                    [
                        'name' => DateGreaterThan::class,
                        'options' => [
                            'min' => (new DateTime("+2 months"))->format('Y-m-d'),
                            'inclusive' => true,
                            'messages' => [
                                DateGreaterThan::NOT_GREATER => "La soutenance doit être déclarée deux mois avant qu'elle est lieu",
                                DateGreaterThan::NOT_GREATER_INCLUSIVE => "La soutenance doit être déclarée deux mois avant qu'elle est lieu",
                            ],
                            //'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ],
        ]));
    }

//    public function getInputFilterSpecification()
//    {
//        return [
//            'date' => [
//                'name' => 'date',
//                'required' => true,
//                'validators' => [
//                    [
//                        'name' => GreaterThan::class,
//                        'options' => [
//                            'min' => new DateTime("+2 months"),
//                            'inclusive' => true,
//                            'messages' => [
//                                GreaterThan::NOT_GREATER => "La soutenance doit être déclarée deux mois avant qu'elle est lieu",
//                                GreaterThan::NOT_GREATER_INCLUSIVE => "La soutenance doit être déclarée deux mois avant qu'elle est lieu",
//                            ],
//                            //'break_chain_on_failure' => true,
//                        ],
//                    ],
//                ],
//            ],
//            'heure' => [
//                'name' => 'heure',
//                'required' => true,
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