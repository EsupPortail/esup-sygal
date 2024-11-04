<?php

namespace Structure\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class VariableForm extends Form {

    use EtablissementServiceAwareTrait;

    public function init()
    {
        $this->add([
            'type' => Text::class,
            'name' => 'code',
            'options' => [
                'label' => "Code * :",
            ],
            'attributes' => [
                'id' => 'code',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'description',
            'options' => [
                'label' => "Description * :",
            ],
            'attributes' => [
                'id' => 'description',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'valeur',
            'options' => [
                'label' => "Valeur * :",
            ],
            'attributes' => [
                'id' => 'valeur',
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'dateDebutValidite',
            'options' => [
                'label' => "Date de début de validité :",
            ],
            'attributes' => [
                'readonly' => 'true',
                'value' => date_create()
            ]
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'dateFinValidite',
            'options' => [
                'label' => "Date de fin de validité :",
            ],
            'attributes' => [
                'readonly' => 'true',
                'value' => date_create()->modify('+10 years')
            ]
        ]);

        $this->add(new Csrf('security'));

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'code' => [
                'required' => true,
            ],
            'description' => [
                'required' => true,
            ],
            'dateDebutValidite' => [
                'required' => false,
            ],
            'valeur' => [
                'required' => false,
            ],
            'dateFinValidite' => [
                'required' => false,
            ],
        ]));
    }
}