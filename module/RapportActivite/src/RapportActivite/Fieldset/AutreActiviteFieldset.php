<?php

namespace RapportActivite\Fieldset;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use RapportActivite\Entity\AutreActivite;
use RapportActivite\Hydrator\AutreActiviteHydrator;

class AutreActiviteFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('autreActivite');

        $this->setHydrator(new AutreActiviteHydrator(false));
        $this->setObject(new AutreActivite());
    }

    public function init()
    {
        $this->add([
            'type' => Text::class,
            'name' => 'nature',
            'options' => [
                'label' => "Nature / Description <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'lieu',
            'options' => [
                'label' => "Lieu / Location <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'public',
            'options' => [
                'label' => "Public concerné / Audience <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Number::class,
            'name' => 'temps',
            'options' => [
                'label' => "Temps consacré (en heures) / Time invested (in hours) <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'min' => 1,
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'date',
            'options' => [
                'label' => "Date <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'nature' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'lieu' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'public' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'temps' => [
                'required' => true,
                'validators' => [
                    ['name' => IsInt::class],
                    ['name' => GreaterThan::class, 'options' => ['min' => 1, 'inclusive' => true]],

                ],
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'date' => [
                'required' => true,
            ],
        ];
    }
}