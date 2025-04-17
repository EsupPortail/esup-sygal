<?php

namespace Formation\Form\Formation\FormationExterne;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\MimeType;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;

class FormationExterneForm extends Form implements InputFilterProviderInterface {
    use EtablissementServiceAwareTrait;
    use StructureServiceAwareTrait;

    public function init()
    {
        //titre
        $this->add([
            'type' => Text::class,
            'name' => 'libelle',
            'options' => [
                'label' => "Libellé de la formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
            ],
            'attributes' => [
                'id' => 'libelle',
                'class' => 'required',
            ],
        ]);
        //debut
        $this->add([
            'name' => 'debut',
            'type' => Date::class,
            'options' => [
                'label' => "Début de la formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'id' => 'debut',
                'class' => 'form-control',
            ],
        ]);
        //fin
        $this->add([
            'name' => 'fin',
            'type' => Date::class,
            'options' => [
                'label' => "Fin de la formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'id' => 'fin',
                'class' => 'form-control',
            ],
        ]);
        //fin
        $this->add([
            'name' => 'duree',
            'type' => Number::class,
            'options' => [
                'label' => "Durée de suivi de la formation de la séance (en heure) <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'id' => 'duree',
                'class' => 'form-control',
            ],
        ]);
        $this->add([
            'type' => File::class,
            'name' => 'attestation',
            'options' => [
                'label' => "Attestation de suivi de la formation (au format PDF, DOCX, ODT)  <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'id' => 'attestation',
            ],
        ]);
        //type
        $this->add([
            'type' => Select::class,
            'name' => 'type',
            'options' => [
                'label' => "Type de formation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Non précisé",
                'value_options' => ['T' => 'Transversale', 'S' => 'Spécifique'],
            ],
            'attributes' => [
                'id' => 'type',
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
            ],
        ]);

        FormUtils::addSaveButton($this);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'libelle' => ['required' => true,],
            'type' => ['required' => true,],
            'debut' => ['required' => true,],
            'fin' => ['required' => true,],
            'duree' => ['required' => true,],
            'attestation' => [
                'type' => FileInput::class,
                'required' => true,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf', 'docx', 'odt'],
                            'messages' => [
                                Extension::FALSE_EXTENSION => "Le type de votre fichier n'est pas autorisé.",
                            ],
                            'break_chain_on_failure' => true,
                        ],
                    ],
                    [
                        'name' => MimeType::class,
                        'options' => [
                            'mimeType' => [
                                'application/pdf', // pdf
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
                                'application/vnd.oasis.opendocument.text', // odt
                            ],
                            'magicFile' => false,
                            'enableHeaderCheck' => true,
                            'message' => "Le format %type% de votre fichier n'est pas autorisé.",
                            'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ]
        ];
    }
}