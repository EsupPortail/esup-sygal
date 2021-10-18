<?php

namespace Soutenance\Form\Justificatif;

use Application\Entity\Db\NatureFichier;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class JustificatifForm extends Form {
    use PropositionServiceAwareTrait;

    public function init()
    {
        //Nature
        $this->add([
            'type' => Select::class,
            'name' => 'nature',
            'options' => [
                'label' => 'Type de justificatif* :',
                'empty_option' => 'Nature du justificatif',
                'value_options' => [
                    NatureFichier::CODE_JUSTIFICATIF_HDR => 'Justificatif d\'habilitation à diriger des recherches ou équivalent',
                    NatureFichier::CODE_JUSTIFICATIF_EMERITAT => 'Justificatif d\'émeritat ou équivalent',
                    NatureFichier::CODE_JUSTIFICATIF_ETRANGER => 'Justificatif permettant de justifier du rang d\'un membre de jury étranger',
                    NatureFichier::CODE_DELOCALISATION_SOUTENANCE => 'Demande de délocalisation de la soutenance',
                    NatureFichier::CODE_DELEGUATION_SIGNATURE => 'Demande de délégation de signature',
                    NatureFichier::CODE_DEMANDE_LABEL => 'Demande de label européen',
                    NatureFichier::CODE_LANGUE_ANGLAISE => 'Demande de manuscrit ou soutenance en langue anglaise',
                    NatureFichier::CODE_DEMANDE_CONFIDENT => 'Demande de confidentialité',
                    NatureFichier::CODE_AUTRES_JUSTIFICATIFS => 'Autre justificatif lié à la soutenance',
                ],
                'attributes' => [
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                    'id' => 'nature',
                ],
            ],
        ]);
        //membre
        $this->add([
            'type' => Select::class,
            'name' => 'membre',
            'options' => [
                'label' => 'Associer à un membre du jury :',
                'empty_option' => 'Aucun',
                'value_options' => ($this->getObject()?$this->getPropositionService()->getMembresAsOptions($this->getObject()->getProposition()):null),
                'attributes' => [
                    'class' => 'bootstrap-selectpicker show-tick',
                    'data-live-search' => 'true',
                ],
            ],
        ]);

        //Fichier
        $this->add([
            'type' => File::class,
            'name' => 'fichier',
            'options' => [
                'label' => 'Fichier* :',
            ],
        ]);
        //SUBMIT
        $this->add([
            'type' => Button::class,
            'name' => 'enregistrer',
            'options' => [
                'label' => 'Téléverser votre fichier',
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
            'nature'    => [
                'name' => 'nature',
                'required' => true,
            ],
            'fichier'   => [
                'name' => 'fichier',
                'required' => false,
            ],
            'membre'    => [
                'name' => 'membre',
                'required' => false,
            ],
        ]));
    }
}