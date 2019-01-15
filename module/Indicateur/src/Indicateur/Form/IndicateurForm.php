<?php

namespace Indicateur\Form;

use Indicateur\Model\Indicateur;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;

class IndicateurForm extends Form {

    public function init() {

        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé de l'indicateur :",
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'description',
            'options' => [
                'label' => "Description de l'indicateur :",
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'requete',
            'options' => [
                'label' => "Requète associé à l'indicateur :",
            ],
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'displayAs',
            'options' => [
                'label' => "Doit être affiché comme :",
                'value_options' => [
                    Indicateur::THESE => 'Thèse',
                    Indicateur::INDIVIDU => 'Individu',
                    Indicateur::STRUCTURE => 'Structure',
                ],
            ],
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'class',
            'options' => [
                'label' => "Niveau d'affichage :",
                'value_options' => [
                    'default'   => 'Défaut',
                    'info'      => 'Informatif',
                    'warning'   => 'Avertissement',
                    'danger'    => 'Problème',
                    'success'    => 'Succès',
                ],
            ],
        ]);

        $this
            ->add((
            new Submit('submit'))
                ->setValue("Enregistrer")
                ->setAttribute('class', 'btn btn-primary')
            );
    }
}