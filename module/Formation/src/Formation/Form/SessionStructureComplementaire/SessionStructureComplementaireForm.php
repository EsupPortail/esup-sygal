<?php

namespace Formation\Form\SessionStructureComplementaire;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Structure\Service\Structure\StructureServiceAwareTrait;

class SessionStructureComplementaireForm extends Form {
    use StructureServiceAwareTrait;

    public function init()
    {
        //site
        $this->add([
            'type' => Select::class,
            'name' => 'structure',
            'options' => [
                'label' => "Structure à associé à la session <span class='icon icon-star' style='color: darkred;' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'empty_option' => "Aucun établissement",
                'value_options' => $this->getStructureService()->getStructuresFormationsAsOptions(),
            ],
            'attributes' => [
                'id' => 'structure',
                'class' => 'show-tick',
                'data-live-search' => 'true',
            ],
        ]);
        //description
        $this->add([
            'type' => Text::class,
            'name' => 'lieu',
            'options' => [
                'label' => "Lieu ou lien de la session pour la structure sélectionnée :",
            ],
            'attributes' => [
                'id' => 'lieu',
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
            'structure'     => [ 'required' => true, ],
            'lieu'          => [ 'required' => false, ],
        ]));
    }
}