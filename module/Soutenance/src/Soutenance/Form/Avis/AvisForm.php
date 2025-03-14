<?php

namespace Soutenance\Form\Avis;

use Application\Utils\FormUtils;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class AvisForm extends Form {

    public function init()
    {
        //AVIS
        $this->add([
            'type' => Radio::class,
            'name' => 'avis',
            'options' => [
                'label' => 'Avis / Notification :',
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
                'label' => 'Déposez le rapport de soutenance / Upload the report',
            ],
        ]);

        FormUtils::addSaveButton($this);

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