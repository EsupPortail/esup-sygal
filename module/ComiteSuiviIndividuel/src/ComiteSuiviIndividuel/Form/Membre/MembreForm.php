<?php

namespace ComiteSuiviIndividuel\Form\Membre;

use Application\Utils\FormUtils;
use ComiteSuiviIndividuel\Entity\Db\Membre;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Laminas\Validator\EmailAddress;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MembreForm extends Form {
    use EntityManagerAwareTrait;
    use QualiteServiceAwareTrait;

    public function init()
    {
        $this->add([
           'type' => Radio::class,
           'name' => 'sexe',
           'options' => [
               'label' => "Civilité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> : ",
               'value_options' => [
                   'F' => 'Madame',
                   'H' => 'Monsieur',
               ],
               'label_options' => [
                   'disable_html_escape' => true,
               ],
           ],
        ]);

        $this->add(
            (new Text('prenom'))
                ->setLabel("Prénom du membre de comité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
        );
        $this->add(
            (new Text('nom'))
                ->setLabel("Nom du membre de comité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
        );


        $mailValidator = new EmailAddress();
        $mailValidator->setMessages([
           EmailAddress::INVALID_FORMAT =>  'Adresse électronique non valide !',
        ]);
        $this->add(
            (new Email('email'))
                ->setLabel("Adresse électronique <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
                ->setValidator($mailValidator)
        );

        $this->add([
            'name' => 'qualite',
            'type' => Select::class,
            'options' => [
                'label' => "Qualité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> : ",
                'empty_option' => "Sélectionner une qualité ... ",
                'value_options' => $this->getQualiteService()->getQualitesAsGroupOptions(),
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id'                => 'competence',
                'class'             => 'bootstrap-selectpicker show-tick',
                'data-live-search'  => 'true',
            ]
        ]);

        $this->add(
            (new Text('etablissement'))
                ->setLabel("Université, établissement d'enseignement ou entreprise <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
        );
        $this->add(
            (new Radio('exterieur'))
                ->setLabel("Le membre est extérieur (non membre d'un établissement de la COMUE et non membre de l'unité de recherche de la thèse) <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setValueOptions([ 'oui' => 'Oui', 'non' => 'Non'])
                ->setLabelOptions(['disable_html_escape' => true,])
        );
        $this->add(
            (new Radio('visio'))
                ->setLabel("Le membre sera présent en visioconférence <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setValueOptions([ '1' => 'Oui', '0' => 'Non'])
                ->setLabelOptions(['disable_html_escape' => true,])
        );
        $this->add(
            (new Radio('role'))
                ->setLabel("Role dans le comité <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setValueOptions([
                    Membre::RAPPORTEUR_CSI   => 'Rapporteur du comité',
                    Membre::MEMBRE_CSI       => 'Membre du comité',
                ])
                ->setLabelOptions(['disable_html_escape' => true,])
        );

        FormUtils::addSaveButton($this);

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