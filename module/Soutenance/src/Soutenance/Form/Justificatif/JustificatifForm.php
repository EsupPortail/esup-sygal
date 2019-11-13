<?php

namespace Soutenance\Form\Justificatif;

use Application\Entity\Db\NatureFichier;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Form\Element\Button;
use Zend\Form\Element\File;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

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
                    NatureFichier::CODE_JUSTIFICATIF_HDR => 'Justificatif d\'habilitation à diriger des recherches',
                    NatureFichier::CODE_JUSTIFICATIF_EMERITAT => 'Justificatif d\'émeritat',
                    NatureFichier::CODE_DELOCALISATION_SOUTENANCE => 'Demande de délocalisation de la soutenance',
                    NatureFichier::CODE_DELEGUATION_SIGNATURE => 'Demande de déléguation de signature',
                    NatureFichier::CODE_DEMANDE_LABEL => 'Demande de label européen',
                    NatureFichier::CODE_LANGUE_ANGLAISE => 'Demande de manuscrit ou soutenance en langue anglaise',
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