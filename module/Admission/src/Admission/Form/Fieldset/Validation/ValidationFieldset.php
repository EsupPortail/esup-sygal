<?php
namespace Admission\Form\Fieldset\Validation;

use Admission\Form\Fieldset\Verification\VerificationFieldset;
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
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('curicculumVitae'))
                ->setLabel("Curriculum Vitae avec adresse, courriel et n° de téléphone")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('financement'))
                ->setLabel("Justificatif du financement (contrat, attestation de l'employeur)")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('projetThese'))
                ->setLabel("Le projet de thèse et son titre (dactylographiés) 1 à 1 page 1/2 maximum")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('exemplairesConvention'))
                ->setLabel("Convention de formation doctorale")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('exemplairesCharteDoctorat'))
                ->setLabel("Charte du doctorat")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('diplomesRelevesNotesTraduits'))
                ->setLabel("Diplômes et relevés de notes traduits en français avec tampons originaux")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new Textarea('argumentaireDirecteurThese'))
                ->setLabel("Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire")
                ->setAttributes(["id" => "argumentaireDirecteurThese"])
        );

        $this->add(
            (new File('acteNaissance'))
                ->setLabel("Extrait d'acte de naissance")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new File('photocopiePasseport'))
                ->setLabel("Photocopie du passeport (ou de la carte d'identité pour les ressortissants européens)")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );
        $this->add(
            (new File('diplomesTravauxExperiencePro'))
                ->setLabel("Diplômes, travaux et expérience professionnelle détaillés")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        $this->add(
            (new Text('documentsDemandeConfidentialite'))
                ->setLabel("Demande de confidentialité à formuler et motiver dans la convention de formation doctorale")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );
        $this->add(
            (new File('documentsCotutelle'))
                ->setLabel("Formulaire de demande de cotutelle")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );
        $this->add(
            (new File('documentsCoencadrement'))
                ->setLabel("Formulaire de demande de co-encadrement à compléter (dès que le co-encadrant est connu)")
                ->setAttributes([
                    'disabled' => 'disabled'
                ])
        );

        //Circuit signature
        $this->add(
            (new Checkbox('attestationHonneurInformations'))
                ->setValue("J'atteste sur l'honneur l'exactitude des informations renseignées ci-dessus")
                ->setLabel("J'atteste sur l'honneur l'exactitude des informations renseignées ci-dessus")
        );

        $this->add((new Submit('saveAttestationHonneur'))
            ->setValue("Valider")
            ->setAttributes([
                'class' => 'btn btn-success',
                'disabled' => 'disabled'
            ])
        );

        $this->add(
            (new Text('validationGestionnaires'))
                ->setLabel("Vérification effectuée par les gestionnaires")
        );

        $this->add(
            (new Text('validationDirecteurthese'))
                ->setLabel("Validation de la Direction de thèse")
        );

        $this->add(
            (new Text('validationCodirecteur'))
                ->setLabel("Validation de la Co-direction")
        );

        $this->add(
            (new Text('validationUniterecherche'))
                ->setLabel("Validation de l'Unité de recherche")
        );

        $this->add(
            (new Text('validationEcoledoctorale'))
                ->setLabel("Validation de l'École doctorale")
        );

        $this->add(
            (new Text('signaturePresident'))
                ->setLabel("Signature de la Présidence de l'établissement d'inscription")
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationValidation");
        $this->add($verificationFieldset);
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