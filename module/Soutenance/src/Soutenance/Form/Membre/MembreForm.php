<?php

namespace Soutenance\Form\Membre;

use DoctrineModule\Form\Element\ObjectSelect;
use Soutenance\Entity\Qualite;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Email;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class MembreForm extends Form {
    use EntityManagerAwareTrait;

    public function init()
    {
        $this->add([
           'type' => Radio::class,
           'name' => 'sexe',
           'options' => [
               'label' => 'Civilité : ',
               'value_options' => [
                   'F' => 'Madame',
                   'H' => 'Monsieur',
               ],
           ],
        ]);

        $this->add(
            (new Text('prenom'))
                ->setLabel("Prénom du membre de jury :")
        );
        $this->add(
            (new Text('nom'))
                ->setLabel("Nom du membre de jury :")
        );

        $this->add(
            (new Email('email'))
                ->setLabel("Adresse électronique :")
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
                ->setLabel("Université ou établissement d'enseignement de rattachement :")
        );
        $this->add(
            (new Radio('exterieur'))
                ->setLabel("Le membre est extérieur (non membre d'un établissement de la COMUE et non membre de l'unité de recherche de la thèse) :")
                ->setValueOptions([ 'oui' => 'Oui', 'non' => 'Non'])
        );
        $this->add(
            (new Radio('visio'))
                ->setLabel("Le membre sera présent en visioconférence :")
                ->setValueOptions([ '1' => 'Oui', '0' => 'Non'])
        );
        $this->add(
            (new Radio('role'))
                ->setLabel("Role dans le jury :")
                ->setValueOptions([
                    'Rapporteur' => 'rapporteur et membre du jury',
                    'Rapporteur absent' => 'rapporteur non présent à la soutenance',
                    'Membre' => 'membre du jury',
                ])
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