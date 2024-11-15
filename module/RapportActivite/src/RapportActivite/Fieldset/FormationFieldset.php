<?php

namespace RapportActivite\Fieldset;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\GreaterThan;
use RapportActivite\Entity\Formation;
use RapportActivite\Hydrator\FormationHydrator;

class FormationFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('formation');

        $this->setHydrator(new FormationHydrator(false));
        $this->setObject(new Formation());
    }

    public function init()
    {
        $this->add([
            'type' => Text::class,
            'name' => 'intitule',
            'options' => [
                'label' => "Intitule / Description <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
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
                'label' => "Volume horaire / Number of hours <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
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
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'intitule' => [
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
        ];
    }
}