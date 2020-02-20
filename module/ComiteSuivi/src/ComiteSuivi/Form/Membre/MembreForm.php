<?php

namespace ComiteSuivi\Form\Membre;

use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\Membre;
use Zend\Form\Element\Button;
use Zend\Form\Element\Email;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class MembreForm extends Form {
    use DateTimeTrait;

    public function init()
    {
        //comite (hidden)
        $this->add([
            'name' => 'comite',
            'type' => Hidden::class,
        ]);
        // Prenom, Nom, Etablissement
        $this->add([
            'name' => 'prenom',
            'type' => Text::class,
            'options' => [
                'label' => 'Prénom * : ',
            ],
            'attributes' => [
                'id' => 'prenom',
            ],
        ]);
        $this->add([
            'name' => 'nom',
            'type' => Text::class,
            'options' => [
                'label' => 'Nom * : ',
            ],
            'attributes' => [
                'id' => 'nom',
            ],
        ]);
        $this->add([
            'name' => 'etablissement',
            'type' => Text::class,
            'options' => [
                'label' => 'Établissement * : ',
            ],
            'attributes' => [
                'id' => 'etablissement',
            ],
        ]);
        //Rôle
        $this->add([
            'name' => 'role',
            'type' => Select::class,
            'options' => [
                'label' => 'Rôle * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Choisissez un rôle ...",
                'value_options' => [
                    Membre::ROLE_EXAMINATEUR_CODE  => "Examinateur",
                    Membre::ROLE_OBSERVATEUR_CODE  => "Observateur"
                ],
            ],
            'attributes' => [
                'id' => 'role',
            ],
        ]);
        //email
        $this->add([
            'name' => 'email',
            'type' => Email::class,
            'options' => [
                'label' => 'Adresse électronique * : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'id' => 'email',
            ],
        ]);
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
            'prenom' => [
                'required' => true,
            ],
            'nom' => [
                'required' => true,
            ],
            'etablissement' => [
                'required' => true,
            ],
            'role' => [
                'required' => true,
            ],
            'email' => [
                'required' => true,
            ],
        ]));
    }

}