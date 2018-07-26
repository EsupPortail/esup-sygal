<?php

namespace Soutenance\Form\SoutenanceMembre;

use DoctrineModule\Form\Element\ObjectSelect;
use Soutenance\Entity\Qualite;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Form\Element\Email;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class SoutenanceMembreForm extends Form {
    use EntityManagerAwareTrait;

    public function init()
    {
        $this->add(
            (new Radio('sexe'))
                ->setLabel("Genre :")
                ->setValueOptions([ 'F' => 'Femme', 'H' => 'Homme'])
        );

        $this->add(
            (new Text('denomination'))
                ->setLabel("Denomination du membre de jury :")
        );

        $this->add(
            (new Email('email'))
                ->setLabel("Adresse électronique :")
        );

        $this->add(
            (new Text('qualite'))
                ->setLabel("Qualité (Maître de conférences, ...) :")
        );
        $this->add([
            'type' => ObjectSelect::class,
            'name' => 'qualite',
            'options' => [
                'label' => "Qualité du membre du jury :",
                'empty_option' => "Sélectionner une qualité...",
                'object_manager' => $this->getEntityManager(),
                'target_class' => Qualite::class,
                'property' => 'libelle',
                'find_method' => [
                    'name' => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy' => ['id' => 'ASC'],
                    ],
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'qualite',
            ],
        ]);

        $this->add(
            (new Text('etablissement'))
                ->setLabel("Etablissement (Université de Caen Normandie, ...) :")
        );
        $this->add(
            (new Text('exterieur'))
                ->setLabel("Membre exterieur (oui ou non) :")
        );
        $this->add(
            (new Radio('exterieur'))
                ->setLabel("Le membre est extérieur à l'établissement encadrant la thèse :")
                ->setValueOptions([ 'oui' => 'Oui', 'non' => 'non'])
        );
        $this->add(
            (new Text('role'))
                ->setLabel("Rôle (rapporteur, membre, membre absent, ...) :")
        );
        $this->add(
            (new Radio('role'))
                ->setLabel("Rôle du membre du jury :")
                ->setValueOptions([ 'Membre' => 'Membre du jury', 'Rapporteur' => 'Rapporteur'])
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