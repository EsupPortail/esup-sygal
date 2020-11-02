<?php

namespace Soutenance\Form\QualiteLibelleSupplementaire;

use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class QualiteLibelleSupplementaireForm extends Form {

    public function init() {

        //qualite (text non editable)
        $this->add([
            'type' => Text::class,
            'name' => 'qualite',
            'options' => [
                'label' => "Qualité :",
            ],
            'attributes' => [
                'id' => 'qualite',
                'readonly' => 'true',
            ],
        ]);
        //libelle
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libelle supplémentaire :",
            ],
            'attributes' => [
                'id' => 'libelle',
            ],
        ]);
        //submit
        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary',
            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'qualite' => [
                'name' => 'qualite',
                'required' => true,
            ],
            'libelle' => [
                'name' => 'libelle',
                'required' => true,
            ],
        ]));
    }
}