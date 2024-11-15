<?php

namespace Formation\Form\Module;

use Application\Utils\FormUtils;
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
                'label' => "Libellé du module de formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
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

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'libelle'       => [ 'required' => true, ],
            'description'   => [ 'required' => false, ],
            'lien'          => [ 'required' => false, ],
            'mission_enseignement'          => [ 'required' => false, ],
        ]));
    }
}