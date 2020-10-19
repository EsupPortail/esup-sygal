<?php

namespace Soutenance\Form\Avis;

use Zend\Form\Element\Button;
use Zend\Form\Element\File;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class AvisForm extends Form {

    public function init()
    {
        //AVIS
        $this->add([
            'type' => Radio::class,
            'name' => 'avis',
            'options' => [
                'label' => 'Avis :',
                'value_options' => [
                    'Favorable' => 'Favorable / I agree',
                    'Défavorable' => 'Défavorable / I disagree',
                ],
                'attributes' => [
                    'class' => 'radio-inline',
                ],
            ],
        ]);
        //MOTIF
        $this->add([
            'type' => Textarea::class,
            'name' => 'motif',
            'options' => [
                'label' => "Motif de refus /  Reason for rejection :",
            ],
            'attributes' => [
                'id' => 'motif',
            ],
        ]);
        //RAPPORT
        $this->add([
            'type' => File::class,
            'name' => 'rapport',
            'options' => [
                'label' => 'Déposez le rapport de soutenance / Download the PhD report',
            ],
        ]);
        //SUBMIT
        $this->add([
            'type' => Button::class,
            'name' => 'enregistrer',
            'options' => [
                'label' => 'Validation',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-success',
            ],
        ]);
        $this->setInputFilter((new Factory())->createInputFilter([
            'avis' => [
                'required' => true,
            ],
            'rapport' => [
                'required' => false,
            ],
            'motif' => [
                'required' => false,
            ],
        ]));
    }
}