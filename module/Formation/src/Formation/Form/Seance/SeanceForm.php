<?php

namespace Formation\Form\Seance;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Element\Time;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class SeanceForm extends Form {

    public function init()
    {
        //jour
        $this->add([
            'name' => 'date',
            'type' => Date::class,
            'options' => [
                'label' => "Date de la séance de formation <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'format' => 'Y-m-d',
            ],
            'attributes' => [
                'id' => 'date',
                'class' => 'form-control',
            ],
        ]);
        //debut
        $this->add([
            'name' => 'debut',
            'type' => Time::class,
            'options' => [
                'label' => "Début de la séance <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
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
            'type' => Time::class,
            'options' => [
                'label' => "Fin de la séance <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
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
        //lien
        $this->add([
            'type' => Text::class,
            'name' => 'lien',
            'options' => [
                'label' => "Lien pour la visio-conférence :",
            ],
            'attributes' => [
                'id' => 'lien',
            ],
        ]);
        //lien
        $this->add([
            'type' => Text::class,
            'name' => 'mot_de_passe',
            'options' => [
                'label' => "Mot de passe pour la visio-conférence :",
            ],
            'attributes' => [
                'id' => 'mot_de_passe',
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
            'lien'          => [ 'required' => false, ],
            'mot_de_passe'  => [ 'required' => false, ],
            'description'   => [ 'required' => false, ],
        ]));
    }
}