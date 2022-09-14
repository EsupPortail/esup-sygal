<?php

namespace These\Fieldset\Confidentialite;

use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class ConfidentialiteFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'type' => Radio::class,
            'name' => 'confidentialite',
            'options' => [
                'label' => "Confidentialité de la thèse :",
                'value_options' => [
                    0 => "These non confidentielle ",
                    1 => "Thèse confidentielle ",
                ],
            ],
            'attributes' => [
                'id' => 'confidentialite',
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'fin-confidentialite',
            'options' => [
                'label' => "Date de fin de confidentialité : ",
            ],
            'attributes' => [
                'id' => 'fin-confidentialite',
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'confidentialite' => [
                'name' => 'confidentialite',
                'required' => false,
            ],
            'fin-confidentialite' => [
                'name' => 'fin-confidentialite',
                'required' => false,
            ],
        ];
    }
}