<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use Zend\Form\Element\Button;
use Zend\Form\Element\File;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
//use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class CompteRenduForm extends Form {
    use DateTimeTrait;
    use MembreServiceAwareTrait;

    public function init()
    {
        //fichier (hidden)
        $this->add([
            'name' => 'fichier',
            'type' => Hidden::class,
        ]);
        //année de thèse
        $this->add([
            'name' => 'examinateur',
            'type' => Select::class,
            'options' => [
                'label' => 'Examinateur * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Choisissez un examinateur ...",
                'value_options' => [],
            ],
            'attributes' => [
                'id' => 'examinateur',
            ],
        ]);
        //RAPPORT
        $this->add([
            'type' => File::class,
            'name' => 'compte_rendu',
            'options' => [
                'label' => 'Déposez le compte-rendu',
            ],
        ]);
        //Ancien système ou le rapport était taper dans l'application ... a voir
//        $this->add([
//            'name' => 'reponse',
//            'type' => Textarea::class,
//            'options' => [
//                'label' => 'Compte-Rendu * : ',
//                'label_attributes' => [
//                    'class' => 'control-label word',
//                ],
//            ],
//            'attributes' => [
//                'id' => 'reponse',
//            ],
//        ]);
        //submit
        $this->add([
            'type' => Button::class,
            'name' => 'creer',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer' ,
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
            'fichier' => [
                'required' => true,
            ],
            'examinateur' => [
                'required' => true,
            ],
            'compte_rendu' => [
                'required' => false,
            ],
        ]));
    }

}