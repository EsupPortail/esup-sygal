<?php

namespace Soutenance\Form\Confidentialite;

use Application\Utils\FormUtils;
use DateInterval;
use DateTime;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Soutenance\Validator\DateLesserThan;

class ConfidentialiteForm extends Form
{

    public function init()
    {
        $minDate = new DateTime();
        $maxDate = new DateTime();
        $maxDate->add(new DateInterval('P10Y'));

        $this->add([
            'type' => \Laminas\Form\Element\Date::class,
            'name' => 'date',
            'options' => [
                'label' => "Date de fin de confidentialité :",
            ],
            'attributes' => [
                'min' => $minDate->format('Y-m-d'),
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

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'date' => [
                'name' => 'date',
                'required' => false,
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