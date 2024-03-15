<?php

namespace Soutenance\Form\DateLieu;

use DateTime as DDateTime;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Time;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;

class DateLieuForm extends Form implements InputFilterProviderInterface
{
    private bool $dateHeureRequired = true;

    public function setDateHeureRequired(bool $required): self
    {
        $this->dateHeureRequired = $required;
        return $this;
    }

    public function init(): void
    {
        $this->add([
            'name' => 'date',
            'type' => Date::class,
            'options' => [
                'label' => 'Date de la soutenance : ',
                'format' => 'Y-m-d',
            ],
            'attributes' => [
                //'min'  => $twomonth->format('Y-m-d'),
            ]
        ]);

        $this->add([
            'name' => 'heure',
            'type' => Time::class,
            'options' => [
                'label' => 'Heure de la soutenance : ',
                'format' => 'H:i',
            ],
        ]);

        $this->add([
            'name' => 'lieu',
            'type' => Text::class,
            'options' => [
                'label' => 'Lieu de la soutenance : ',
            ],
        ]);

        $this->add([
            'name' => 'exterieur',
            'type' => Radio::class,
            'options' => [
                'label' => 'La soutenance aura lieu :',
                'value_options' => [
                    '0' => 'dans l\'établissement d\'encadrement',
                    '1' => 'hors l\'établissement d\'encadrement',
                ],
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
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'date' => [
                'required' => $this->dateHeureRequired,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => "La date de soutenance est dans le passé ! ",
                            ],
                            'callback' => function ($value) {
                                $sdate = DDateTime::createFromFormat('Y-m-d', $value);
                                $cdate = new DDateTime();
                                $res = $sdate >= $cdate;
                                return $res;
                            },
                        ],
                    ],
                ],
            ],
            'heure' => [ 'required' => $this->dateHeureRequired, ],
            'lieu' => [ 'required' => true, ],
            'exterieur' => [ 'required' => true, ],
        ];
    }
}