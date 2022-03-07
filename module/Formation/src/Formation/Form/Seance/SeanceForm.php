<?php

namespace Formation\Form\Seance;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\DateTime;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class SeanceForm extends Form {

    public function init()
    {
        //jour
        $this->add([
            'name' => 'date',
            'type' => DateTime::class,
            'options' => [
                'label' => 'Date de la séance de formation * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'format' => 'd/m/Y',
            ],
            'attributes' => [
                'id' => 'date',
                'class' => 'form-control',
            ],
        ]);
        //debut
        $this->add([
            'name' => 'debut',
            'type' => DateTime::class,
            'options' => [
                'label' => 'Début de la séance * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'format' => 'H:i',
            ],
            'attributes' => [
                'id' => 'debut',
                'class' => 'form-control',
            ],
        ]);
        //fin
        $this->add([
            'name' => 'fin',
            'type' => DateTime::class,
            'options' => [
                'label' => 'Fin de la séance * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'format' => 'H:i',
            ],
            'attributes' => [
                'id' => 'fin',
                'class' => 'form-control',
            ],
        ]);
        //lieu
        $this->add([
            'type' => Text::class,
            'name' => 'lieu',
            'options' => [
                'label' => "Lieu de la séance :",
            ],
            'attributes' => [
                'id' => 'lieu',
            ],
        ]);
        //description
        $this->add([
            'type' => Textarea::class,
            'name' => 'description',
            'options' => [
                'label' => "Complément d'information à propos de la séance :",
            ],
            'attributes' => [
                'id' => 'description',
                'class' => 'tinymce',
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
            'date'          => [ 'required' => true, ],
            'debut'         => [ 'required' => true, ],
            'fin'           => [ 'required' => true, ],
            'lieu'          => [ 'required' => false, ],
            'description'   => [ 'required' => false, ],
        ]));
    }
}