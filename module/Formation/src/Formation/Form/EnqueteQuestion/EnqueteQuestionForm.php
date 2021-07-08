<?php

namespace Formation\Form\EnqueteQuestion;

use Zend\Form\Element\Button;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class EnqueteQuestionForm extends Form {

    public function init()
    {
        //libelle
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé de la question :",
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
                'label' => "Complément d'information à propos de la question :",
            ],
            'attributes' => [
                'id' => 'description',
                'class' => 'tinymce',
            ],
        ]);
        //ordre
        $this->add([
            'type' => Text::class,
            'name' => 'ordre',
            'options' => [
                'label' => "Ordre de la question *:",
            ],
            'attributes' => [
                'id' => 'ordre',
                'class' => 'required',
            ],
        ]);
        //submit
        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',

            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'libelle'       => [ 'required' => true, ],
            'description'   => [ 'required' => false, ],
            'ordre'         => [ 'required' => true, ],
        ]));
    }
}