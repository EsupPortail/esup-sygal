<?php

namespace Application\Form;

use Zend\Form\Element\Button;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class InformationForm extends Form {

    public function init()
    {
        // titre
        $this->add([
            'type' => Text::class,
            'name' => 'titre',
            'options' => [
                'label' => "Titre :",
            ],
            'attributes' => [
                'id' => 'titre',
            ],
        ]);
        // contenu
        $this->add([
            'name' => 'contenu',
            'type' => 'textarea',
            'options' => [
                'label' => 'Contenu : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
//                'class' => 'type2 form-control',
                'class' => 'form-control',
            ]
        ]);
        // button
        $this->add([
            'type' => Button::class,
            'name' => 'creer',
            'options' => [
                'label' => 'Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}