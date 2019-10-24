<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Zend\Form\Element\Button;
use Zend\Form\Element\Email;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class ActeurSimuleForm extends Form {
    use RoleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function init()
    {
        $this->add(
            [
                'type' => Radio::class,
                'name' => 'civilite',
                'options' => [
                    'label' => 'Civilité :',
                    'value_options' => [
                        'Mme' => 'Madame',
                        'M.'  => 'Monsieur',
                    ],
                    'attributes' => [
                        'class' => 'radio-inline',
                    ],
                ],
            ]
        );
        // prenom
        $this->add([
            'type' => Text::class,
            'name' => 'prenom',
            'options' => [
                'label' => "Prénom* :",
            ],
            'attributes' => [
                'id' => 'prenom',
            ],
        ]);
        // nom
        $this->add([
            'type' => Text::class,
            'name' => 'nom',
            'options' => [
                'label' => "Nom* :",
            ],
            'attributes' => [
                'id' => 'nom',
            ],
        ]);
        //email
        $this->add([
            'type' => Email::class,
            'name' => 'email',
            'options' => [
                'label' => "Adresse électronique :",
            ],
            'attributes' => [
                'id' => 'email',
            ],
        ]);
        //role
        $this->add([
            'name' => 'role',
            'type' => Select::class,
            'options' => [
                'label' => 'Role* : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Sélectionner un role ... ",
                'value_options' => $this->getRoleService()->getRolesAsGroupOptions(),
            ],
            'attributes' => [
                'id'                => 'role',
                'class'             => 'bootstrap-selectpicker',
                'data-live-search'  => 'true',
            ]
        ]);
        //qualite
        $this->add([
            'type' => Text::class,
            'name' => 'qualite',
            'options' => [
                'label' => "Qualité :",
            ],
            'attributes' => [
                'id' => 'qualite',
            ],
        ]);
        //etablissement
        $this->add([
            'name' => 'etablissement',
            'type' => Select::class,
            'options' => [
                'label' => 'Établissement : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Sélectionner un établissement ... ",
                'value_options' => $this->getEtablissementService()->getEtablissementAsOptions(),
            ],
            'attributes' => [
                'id'                => 'etablissement',
                'class'             => 'bootstrap-selectpicker',
                'data-live-search'  => 'true',
            ]
        ]);

        //button
        $this->add([
            'type' => Button::class,
            'name' => 'creer',
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
            'civilite'      => [ "required" => false ],
            'prenom'        => [ "required" => true ],
            'nom'           => [ "required" => true ],
            'email'         => [ "required" => false ],
            'role'          => [ "required" => true ],
            'qualite'       => [ "required" => false ],
            'etablissement' => [ "required" => false ],
        ]));
    }
}