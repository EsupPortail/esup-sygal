<?php
namespace Admission\Form\Fieldset\Validation;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;

class ValidationFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        //Pièces justificatives
        $this->add(
            (new File('diplomeBac'))
                ->setLabel("Copie du diplôme de Bac + 5 permettant l'accès au doctorat")
        );

        $this->add(
            (new File('curicculumVitae'))
                ->setLabel("Curriculum Vitae avec adresse, courriel et n° de téléphone")
        );

        $this->add(
            (new File('financement'))
                ->setLabel("Justificatif du financement (contrat, attestation de l'employeur)")
        );

        $this->add(
            (new File('projetThese'))
                ->setLabel("Le projet de thèse et son titre (dactylographiés) 1 à 1 page 1/2 maximum")
        );

        $this->add(
            (new File('exemplairesConvention'))
                ->setLabel("Deux exemplaires de la convention de formation doctorale")
        );

        $this->add(
            (new File('exemplairesCharteDoctorat'))
                ->setLabel("Deux exemplaires de la charte du doctorat")
        );

        $this->add(
            (new File('diplomesRelevesNotesTraduits'))
                ->setLabel("Diplômes et relevés de notes traduits en français avec tampons originaux")
        );

        $this->add(
            (new Textarea('argumentaireDirecteurThese'))
                ->setLabel("Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire")
                ->setAttributes(["id" => "argumentaireDirecteurThese"])
        );

        $this->add(
            (new File('acteNaissance'))
                ->setLabel("Extrait d'acte de naissance")
        );

        $this->add(
            (new File('photocopiePasseport'))
                ->setLabel("Photocopie du passeport (ou de la carte d'identité pour les ressortissants européens)")
        );
        $this->add(
            (new File('diplomesTravauxExperiencePro'))
                ->setLabel("Diplômes, travaux et expérience professionnelle détaillés")
        );

        $this->add(
            (new Text('documentsDemandeConfidentialite'))
                ->setLabel("Demande de confidentialité à formuler et motiver dans la convention de formation doctorale")
        );
        $this->add(
            (new File('documentsCotutelle'))
                ->setLabel("Formulaire de demande de cotutelle")
        );
        $this->add(
            (new File('documentsCoencadrement'))
                ->setLabel("Formulaire de demande de co-encadrement à compléter (dès que le co-encadrant est connu)")
        );

        //Circuit signature
        $this->add(
            (new Checkbox('attestationHonneurInformations'))
                ->setValue("J'atteste sur l'honneur l'exactitude des informations renseignées ci-dessus")
                ->setLabel("J'atteste sur l'honneur l'exactitude des informations renseignées ci-dessus")
        );

        $this->add((new Submit('saveAttestationHonneur'))
            ->setValue("Valider")
            ->setAttribute('class', 'btn btn-success')
        );

        $this->add(
            (new Text('validationGestionnaires'))
                ->setLabel("Validation des gestionnaires")
        );

        $this->add(
            (new Text('validationDirecteurthese'))
                ->setLabel("Validation du directeur de thèse")
        );

        $this->add(
            (new Text('validationCodirecteur'))
                ->setLabel("Validation du co-directeur")
        );

        $this->add(
            (new Text('validationUniterecherche'))
                ->setLabel("Validation de l'Unité de recherche")
        );

        $this->add(
            (new Text('validationEcoledoctorale'))
                ->setLabel("Validation de l'école doctorale")
        );

        $this->add(
            (new Text('signaturePresident'))
                ->setLabel("Signature du président de l'Université")
        );
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            //Pièces justificatives
            'diplomeBac' => [
                'name' => 'diplomeBac',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'curicculumVitae' => [
                'name' => 'curicculumVitae',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'financement' => [
                'name' => 'financement',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'projetThese' => [
                'name' => 'projetThese',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'exemplairesConvention' => [
                'name' => 'exemplairesConvention',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'exemplairesCharteDoctorat' => [
                'name' => 'exemplairesCharteDoctorat',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'diplomesRelevesNotesTraduits' => [
                'name' => 'diplomesRelevesNotesTraduits',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'argumentaireDirecteurThese' => [
                'name' => 'argumentaireDirecteurThese',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'acteNaissance' => [
                'name' => 'acteNaissance',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'photocopiePasseport' => [
                'name' => 'photocopiePasseport',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'diplomesTravauxExperiencePro' => [
                'name' => 'diplomesTravauxExperiencePro',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'documentsCotutelle' => [
                'name' => 'documentsCotutelle',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'documentsCoencadrement' => [
                'name' => 'documentsCoencadrement',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            //Circuit signatures
            'validationGestionnaires' => [
                'name' => 'validationGestionnaires',
                'required' => false,
                'validators' => [
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf','jpg', 'png'],
                        ],
                    ],
                ],
            ],
            'intituleDuDiplome' => [
                'name' => 'intituleDuDiplome',
                'required' => false,
            ],
            'anneeDobtentionDiplome' => [
                'name' => 'anneeDobtentionDiplome',
                'required' => false,
            ],
            'etablissementDobtentionDiplome' => [
                'name' => 'etablissementDobtentionDiplome',
                'required' => false,
            ],
            'typeDiplomeAutre' => [
                'name' => 'typeDiplomeAutre',
                'required' => false,
            ],
        ];
    }
}