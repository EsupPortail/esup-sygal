<?php
namespace Admission\Form\Fieldset\Document;

use Admission\Form\Fieldset\Validation\ValidationFieldset;
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

class DocumentFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->add(
            (new File('diplomeBac'))
                ->setLabel("Copie du diplôme de Bac + 5 permettant l'accès au doctorat")
                ->setAttributes(["id" => "ADMISSION_DIPLOME_BAC"])
        );

        $this->add(
            (new File('curicculumVitae'))
                ->setLabel("Curriculum Vitae avec adresse, courriel et n° de téléphone")
                ->setAttributes(["id" => "ADMISSION_CURRICULUM_VITAE"])
        );

        $this->add(
            (new File('financement'))
                ->setLabel("Justificatif du financement (contrat, attestation de l'employeur)")
                ->setAttributes(["id" => "ADMISSION_FINANCEMENT"])
        );

        $this->add(
            (new File('projetThese'))
                ->setLabel("Le projet de thèse et son titre (dactylographiés) 1 à 1 page 1/2 maximum")
                ->setAttributes(["id" => "ADMISSION_PROJET_THESE"])
        );

        $this->add(
            (new File('exemplairesConvention'))
                ->setLabel("Convention de formation doctorale")
                ->setAttributes(["id" => "ADMISSION_CONVENTION"])
        );

        $this->add(
            (new File('exemplairesCharteDoctorat'))
                ->setLabel("Charte du doctorat")
                ->setAttributes(["id" => "ADMISSION_CHARTE_DOCTORAT"])
        );

        $this->add(
            (new File('diplomesRelevesNotesTraduits'))
                ->setLabel("Diplômes et relevés de notes traduits en français avec tampons originaux")
                ->setAttributes(["id" => "ADMISSION_DIPLOMES_RELEVES_TRADUITS"])
        );

        $this->add(
            (new Textarea('argumentaireDirecteurThese'))
                ->setLabel("Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire")
                ->setAttributes(["id" => "argumentaireDirecteurThese"])
        );

        $this->add(
            (new File('acteNaissance'))
                ->setLabel("Extrait d'acte de naissance")
                ->setAttributes(["id" => "ADMISSION_ACTE_NAISSANCE"])
        );

        $this->add(
            (new File('photocopiePasseport'))
                ->setLabel("Photocopie du passeport (ou de la carte d'identité pour les ressortissants européens)")
                ->setAttributes(["id" => "ADMISSION_PASSEPORT"])
        );
        $this->add(
            (new File('diplomesTravauxExperiencePro'))
                ->setLabel("Diplômes, travaux et expérience professionnelle détaillés")
                ->setAttributes(["id" => "ADMISSION_DIPLOMES_TRAVAUX_EXPERIENCE_PRO"])
        );

        $this->add(
            (new Text('documentsDemandeConfidentialite'))
                ->setLabel("Demande de confidentialité à formuler et motiver dans la convention de formation doctorale")
        );
        $this->add(
            (new File('documentsCotutelle'))
                ->setLabel("Formulaire de demande de cotutelle")
                ->setAttributes(["id" => "ADMISSION_DEMANDE_COTUTELLE"])
        );
        $this->add(
            (new File('documentsCoencadrement'))
                ->setLabel("Formulaire de demande de co-encadrement à compléter (dès que le co-encadrant est connu)")
                ->setAttributes(["id" => "ADMISSION_DEMANDE_COENCADREMENT"])
        );

        $verificationFieldset = $this->getFormFactory()->getFormElementManager()->get(VerificationFieldset::class);
        $verificationFieldset->setName("verificationValidation");
        $this->add($verificationFieldset);

        $validationFieldset = $this->getFormFactory()->getFormElementManager()->get(ValidationFieldset::class);
        $validationFieldset->setName("validation");
        $this->add($validationFieldset);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
//            'diplomeBac' => [
//                'name' => 'diplomeBac',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'curicculumVitae' => [
//                'name' => 'curicculumVitae',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'financement' => [
//                'name' => 'financement',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'projetThese' => [
//                'name' => 'projetThese',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'exemplairesConvention' => [
//                'name' => 'exemplairesConvention',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'exemplairesCharteDoctorat' => [
//                'name' => 'exemplairesCharteDoctorat',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'diplomesRelevesNotesTraduits' => [
//                'name' => 'diplomesRelevesNotesTraduits',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'argumentaireDirecteurThese' => [
//                'name' => 'argumentaireDirecteurThese',
//                'required' => false,
//                'filters' => [
//                    ['name' => StripTags::class],
//                    ['name' => StringTrim::class],
//                ],
//            ],
//            'acteNaissance' => [
//                'name' => 'acteNaissance',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'photocopiePasseport' => [
//                'name' => 'photocopiePasseport',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'diplomesTravauxExperiencePro' => [
//                'name' => 'diplomesTravauxExperiencePro',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'documentsCotutelle' => [
//                'name' => 'documentsCotutelle',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
//            'documentsCoencadrement' => [
//                'name' => 'documentsCoencadrement',
//                'required' => false,
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['pdf','jpg', 'png'],
//                        ],
//                    ],
//                ],
//            ],
        ];
    }
}