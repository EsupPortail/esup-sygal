<?php

namespace Formation\Form\SessionStructureValide;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Structure\Service\Structure\StructureServiceAwareTrait;

class SessionStructureValideForm extends Form {
    use StructureServiceAwareTrait;

    public function init()
    {
        //site
        $this->add([
            'type' => Select::class,
            'name' => 'structure',
            'options' => [
                'label' => "Structure à associé à la session <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'empty_option' => "Aucun établissement",
                'value_options' => $this->getStructureService()->getStructuresFormationsAsOptions(),
            ],
            'attributes' => [
                'id' => 'structure',
                'class' => 'bootstrap-selectpicker show-tick',
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

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'structure'     => [ 'required' => true, ],
            'lieu'          => [ 'required' => false, ],
        ]));
    }
}