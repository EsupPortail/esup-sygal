<?php

namespace ComiteSuiviIndividuel\Form\Membre;

use ComiteSuiviIndividuel\Entity\Db\Membre;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Laminas\Validator\EmailAddress;

class MembreForm extends Form {
    use EntityManagerAwareTrait;
    use QualiteServiceAwareTrait;

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
                ->setLabel("Prénom du membre de comité :")
        );
        $this->add(
            (new Text('nom'))
                ->setLabel("Nom du membre de comité :")
        );


        $mailValidator = new EmailAddress();
        $mailValidator->setMessages([
           EmailAddress::INVALID_FORMAT =>  'Adresse électronique non valide !',
        ]);
        $this->add(
            (new Email('email'))
                ->setLabel("Adresse électronique :")
                ->setValidator($mailValidator)
        );

        $this->add([
            'name' => 'qualite',
            'type' => Select::class,
            'options' => [
                'label' => 'Qualité : ',
                'empty_option' => "Sélectionner une qualité ... ",
                'value_options' => $this->getQualiteService()->getQualitesAsGroupOptions(),
            ],
            'attributes' => [
                'id'                => 'competence',
                'class'             => 'bootstrap-selectpicker show-tick',
                'data-live-search'  => 'true',
            ]
        ]);

        $this->add(
            (new Text('etablissement'))
                ->setLabel("Université, établissement d'enseignement ou entreprise :")
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
                ->setLabel("Role dans le comité :")
                ->setValueOptions([
                    Membre::RAPPORTEUR_CSI   => 'Rapporteur du comité',
                    Membre::MEMBRE_CSI       => 'Membre du comité',
                ])
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'sexe' => [
                'name' => 'sexe',
                'required' => true,
            ],
            'prenom' => [
                'name' => 'prenom',
                'required' => true,
            ],
            'nom' => [
                'name' => 'prenom',
                'required' => true,
            ],
            'email' => [
                'name' => 'email',
                'required' => true,
                'validator' => [
                    'name' => EmailAddress::class,
                    'messages' => [
                        EmailAddress::INVALID_FORMAT => '',
                    ],
                ],
            ],
            'qualite' => [
                'name' => 'qualite',
                'required' => true,
            ],
            'etablissement' => [
                'name' => 'etablissement',
                'required' => true,
            ],
            'exterieur' => [
                'name' => 'exterieur',
                'required' => true,
            ],
            'visio' => [
                'name' => 'visio',
                'required' => true,
            ],
            'role' => [
                'name' => 'role',
                'required' => true,
            ],
        ]));
    }

}