<?php

namespace Soutenance\Form\Confidentialite;

use DateInterval;
use DateTime;
use Soutenance\Validator\DateGreaterThan;
use Soutenance\Validator\DateLesserThan;
use UnicaenApp\Form\Element\Date;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class ConfidentialiteForm extends Form {

    public function init()
    {
        $refDate = new DateTime();
        $maxDate = $refDate->add(new DateInterval('P10Y'));

        $this->add([
            'type' => Date::class,
            'name' => 'date',
            'options' => [
                'label' => "Date de fin de confidentialité :",
            ],
            'attributes' => [
                'min' => $refDate->format('Y-m-d'),
                'max' => $maxDate->format('Y-m-d'),
            ]
        ]);
        $this->add(
            [
                'type' => Checkbox::class,
                'name' => 'huitclos',
                'options' => [
                    'label' => "Soutenance en huis clos",
                ],
                'attributes' => [
                    'id' => 'huitclos',
                ],
            ]
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'date' => [
                'name' => 'date',
                'required' => true,
                'validators' => [
                    [
                        'name' => DateLesserThan::class,
                        'options' => [
                            'max' => (new DateTime("+10 years"))->format('Y-m-d'),
                            'inclusive' => true,
                            'messages' => [
                                DateLesserThan::NOT_LESSER => "La période de confidentialité maximale est de 10 ans.",
                                DateLesserThan::NOT_LESSER_INCLUSIVE => "La période de confidentialité maximale est de 10 ans.",
                            ],
                            //'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ],
            'huitclos' => [
                'required' => false,
            ],
        ]));
    }
}