<?php

namespace Application\Form;

use Structure\Entity\Db\TypeStructure;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class ProfilForm extends Form {

    public function init()
    {
        //libelle
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libelle <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'libelle',
            ],
        ]);
        //profil_id
        $this->add([
            'type' => Text::class,
            'name' => 'code',
            'options' => [
                'label' => "Code <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'code',
            ],
        ]);
        //structure
        $this->add([
            'type' => Select::class,
            'name' => 'structure',
            'options' => [
                'label' => "Type de structure :",
                'value_options' => [
                    '' => "Aucun",
                    TypeStructure::CODE_ECOLE_DOCTORALE  => 'École doctorale',
                    TypeStructure::CODE_ETABLISSEMENT    => 'Établissement',
                    TypeStructure::CODE_UNITE_RECHERCHE  => 'Unité de recherche',
                ],
            ],
            'attributes' => [
                'id' => 'structure',
                'class' => 'selectpicker show-menu-arrow',
                'data-live-search' => 'true',
            ],
        ]);
        //description
        $this->add([
            'name' => 'description',
            'type' => 'textarea',
            'options' => [
                'label' => 'Description du profil : ',
                'label_attributes' => [
                    'class' => 'col-form-label',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
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
            'libelle' => [
                'required' => true,
            ],
            'code' => [
                'required' => true,
            ],
            'structure' => [
                'required' => false,
            ],
            'description' => [
                'required' => false,
            ],
        ]));
    }
}