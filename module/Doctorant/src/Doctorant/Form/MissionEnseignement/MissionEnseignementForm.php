<?php

namespace Doctorant\Form\MissionEnseignement;

use DateTime;
use Laminas\Form\Element\Button;
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
                'label' => "Année_universitaire <span class='icon icon-obligatoire' title='Donnée obligatoire'></span> :",
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
                'class' => 'btn btn-success',
            ],
        ]);

        $this->setInputFilter((new Factory())->createInputFilter([
            'annee_univ' => [ 'required' => true, ],
        ]));
    }
}