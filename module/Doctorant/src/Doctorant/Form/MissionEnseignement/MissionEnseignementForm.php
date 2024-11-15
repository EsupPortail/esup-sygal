<?php

namespace Doctorant\Form\MissionEnseignement;

use Application\Utils\FormUtils;
use DateTime;
use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class MissionEnseignementForm extends Form
{
    public function init(): void
    {

        $date = new DateTime();
        $current = $date->format('Y');
        if ($date->format('M') > 8)  $current++;

        $options = [];
        for ($i = -3 ; $i <=3; $i++) $options[$current + $i] = ($current+$i) ."/".($current+ $i + 1);

        $this->add([
            'type' => Select::class,
            'name' => 'annee_univ',
            'options' => [
                'label' => "Année universitaire concernée <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'value_options' => $options,
                'empty_option' => "Sélectionner une année universitaire",
            ],
            'attributes' => [
                'id' => 'annee_univ',
                'class' => 'selectpicker show-menu-arrow',
                'data-bs-html' => 'true',
            ]
        ]);

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'annee_univ' => [ 'required' => true, ],
        ]));
    }
}