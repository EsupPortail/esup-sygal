<?php

namespace Soutenance\Form\SoutenanceMembre;

use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class SoutenanceMembreForm extends Form {

    public function init()
    {
        $this->add(
            (new Text('sexe'))
                ->setLabel("Genre (homme/femme) :")
        );

        $this->add(
            (new Text('denomination'))
                ->setLabel("Denomindation du membre de jury (prenom NOM :")
        );

        $this->add(
            (new Text('qualite'))
                ->setLabel("Qualité (Maître de conférences, ...) :")
        );
        $this->add(
            (new Text('rang'))
                ->setLabel("Rang (A ou B) :")
        );

        $this->add(
            (new Text('etablissement'))
                ->setLabel("Etablissement (Université de Caen Normandie, ...) :")
        );
        $this->add(
            (new Text('exterieur'))
                ->setLabel("Membre exterieur (oui ou non) :")
        );

        $this->add(
            (new Text('role'))
                ->setLabel("Rôle (rapporteur, membre, membre absent, ...) :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

//        $this->setInputFilter(
//            $this->getInputFilter()
//        );
    }

//    public function getInputFilterSpecification()
//    {
//        return [
//            'date' => [
//                'name' => 'date',
//                'required' => true,
//            ],
//            'heure' => [
//                'name' => 'heure',
//                'required' => true,
//            ],
//            'lieu' => [
//                'name' => 'lieu',
//                'required' => true,
//            ],
//        ];
//    }
}