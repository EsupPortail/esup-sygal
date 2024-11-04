<?php

namespace Soutenance\Form\QualiteLibelleSupplementaire;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

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

        FormUtils::addSaveButton($this);

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