<?php

namespace Formation\Form\Module;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class ModuleForm extends Form {

    public function init()
    {
        //titre
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé du module de formation <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'libelle',
                'class' => 'required',
            ],
        ]);
        //description
        $this->add([
            'type' => Textarea::class,
            'name' => 'description',
            'options' => [
                'label' => "Complément d'information à propos du module :",
            ],
            'attributes' => [
                'id' => 'description',
                'class' => 'tinymce',
            ],
        ]);
        //titre
        $this->add([
            'type' => Text::class,
            'name' => 'lien',
            'options' => [
                'label' => "Lien vers la fiche du module :",
            ],
            'attributes' => [
                'id' => 'lien',
                'class' => 'required',
            ],
        ]);
        //titre
        $this->add([
            'type' => Checkbox::class,
            'name' => 'mission_enseignement',
            'options' => [
                'label' => "Les doctorants doivent avoir une mission d'enseignement déclarée",
            ],
            'attributes' => [
                'id' => 'mission_enseignement',
            ],
        ]);

        //submit
        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',

            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'libelle'       => [ 'required' => true, ],
            'description'   => [ 'required' => false, ],
            'lien'          => [ 'required' => false, ],
            'mission_enseignement'          => [ 'required' => false, ],
        ]));
    }
}