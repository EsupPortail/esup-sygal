<?php

namespace UnicaenAvis\Fieldset;

use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilterProviderInterface;

class AvisValeurFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @var \UnicaenAvis\Entity\Db\AvisValeur
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
            'name' => 'valeur',
            'options' => [
                'label' => "Valeur :",
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
            'name' => 'valeurBool',
            'options' => [
                'label' => "Valeur booléenne :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'value_options' => [
                    '' => "Non pertinent",
                    false => 'false',
                    true => 'true',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'tags',
            'options' => [
                'label' => "Tags :",
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

        $this->add([
            'type' => Textarea::class,
            'name' => 'description',
            'options' => [
                'label' => "Description :",
                'label_attributes' => [
                    //'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 3,
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
            'valeur' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'valeurBool' => [
                'required' => false,
                'filters' => [
                    ['name' => ToNull::class, 'options' => ['type' => ToNull::TYPE_STRING]],
                ],
            ],
            'tags' => [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'description' => [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
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