<?php

namespace UnicaenAvis\Fieldset;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\Collection;

class AvisTypeFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @var \UnicaenAvis\Entity\Db\AvisType
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

        $avisTypeValeurs = new Collection('avisValeurs');
        $avisTypeValeurs
            ->setLabel("Valeurs possibles : ")
            ->setMinElements(0)
            ->setOptions([
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(AvisValeurFieldset::class),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($avisTypeValeurs);
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