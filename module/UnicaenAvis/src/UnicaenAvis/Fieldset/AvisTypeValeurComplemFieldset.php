<?php

namespace UnicaenAvis\Fieldset;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenAvis\Entity\Db\AvisTypeValeurComplem;

class AvisTypeValeurComplemFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @var \UnicaenAvis\Entity\Db\AvisTypeValeurComplem
     */
    protected $object;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->add([
            'type' => Text::class,
            'name' => 'code',
            'options' => [
                'label' => "Code unique :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'type',
            'options' => [
                'label' => "Type :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'value_options' => array_combine($v = [
                AvisTypeValeurComplem::TYPE_COMPLEMENT_TEXTAREA,
                AvisTypeValeurComplem::TYPE_COMPLEMENT_CHECKBOX,
                AvisTypeValeurComplem::TYPE_COMPLEMENT_INFORMATION,
            ], $v),
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Checkbox::class,
            'name' => 'obligatoire',
            'options' => [
                'label' => "Obligatoire :",
                'label_attributes' => [
                    //'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Checkbox::class,
            'name' => 'obligatoireUnAuMoins',
            'options' => [
                'label' => "Une valeur est requise pour l'un au moins des compléments ayant ce témoin à `true`",
                'label_attributes' => [
                    //'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Number::class,
            'name' => 'ordre',
            'options' => [
                'label' => "Ordre d'affichage :",
                'label_attributes' => [
                    //'class' => 'required',
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
    public function getInputFilterSpecification(): array
    {
        return [
            'code' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'libelle' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'type' => [
                'required' => true,
            ],
            'obligatoire' => [
                'required' => false,
            ],
            'obligatoireUnAuMoins' => [
                'required' => false,
            ],
            'ordre' => [
                'required' => false,
                'validators' => [
                    ['name' => IsInt::class],
                ],
            ],
        ];
    }
}