<?php
namespace Admission\Fieldset\Justificatifs;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;

class PiecesJustificativesFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {
        $this->add(
            (new File('diplome_bac'))
                ->setLabel("Copie du diplôme de Bac + 5 permettant l'accès au doctorat")
        );

        $this->add(
            (new File('curicculum_vitae'))
                ->setLabel("Curriculum Vitae avec adresse, courriel et n° de téléphone")
        );

        $this->add(
            (new File('financement'))
                ->setLabel("Justificatif du financement (contrat, attestation de l'employeur)")
        );

        $this->add(
            (new File('projet_these'))
                ->setLabel("Le projet de thèse et son titre (dactylographiés) 1 à 1 page 1/2 maximum")
        );

        $this->add(
            (new File('exemplaires_convention'))
                ->setLabel("Deux exemplaires de la convention de formation doctorale")
        );

        $this->add(
            (new File('exemplaires_charte_doctorat'))
                ->setLabel("Deux exemplaires de la charte du doctorat")
        );

        $this->add(
            (new File('diplomes_releves_notes_traduits'))
                ->setLabel("Diplômes et relevés de notes traduits en français avec tampons originaux")
        );

        $this->add(
            (new Textarea('argumentaire_directeur_these'))
                ->setLabel("Argumentaire du directeur de thèse pour le conseil de l'école doctorale obligatoire")
                ->setAttributes(["id" => "argumentaire_directeur_these"])
        );

        $this->add(
            (new File('acte_naissance'))
                ->setLabel("Extrait d'acte de naissance")
        );

        $this->add(
            (new File('photocopie_passeport'))
                ->setLabel("Photocopie du passeport (ou de la carte d'identité pour les ressortissants européens)")
        );
        $this->add(
            (new File('diplomes_travaux_experience_pro'))
                ->setLabel("Diplômes, travaux et expérience professionnelle détaillés")
        );

        $this->add(
            (new Text('documents_demande_confidentialite'))
                ->setLabel("Si demande de confidentialité : Demande à formuler et motiver dans la convention de formation doctorale")
        );
        $this->add(
            (new File('documents_cotutelle'))
                ->setLabel("Si cotutelle : Formulaire de demande de cotutelle")
        );
        $this->add(
            (new File('documents_coencadrement'))
                ->setLabel("Si co-encadrement : Formulaire de demande de co-encadrement à compléter (dès que le co-encadrant est connu)")
        );


    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'diplome_bac' => [
                'name' => 'diplome_bac',
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
            'curicculum_vitae' => [
                'name' => 'curicculum_vitae',
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
            'projet_these' => [
                'name' => 'projet_these',
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
            'exemplaires_convention' => [
                'name' => 'exemplaires_convention',
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
            'exemplaires_charte_doctorat' => [
                'name' => 'exemplaires_charte_doctorat',
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
            'diplomes_releves_notes_traduits' => [
                'name' => 'diplomes_releves_notes_traduits',
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
            'argumentaire_directeur_these' => [
                'name' => 'argumentaire_directeur_these',
                'required' => false,
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
            ],
            'acte_naissance' => [
                'name' => 'acte_naissance',
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
            'photocopie_passeport' => [
                'name' => 'photocopie_passeport',
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
            'diplomes_travaux_experience_pro' => [
                'name' => 'diplomes_travaux_experience_pro',
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
            'documents_cotutelle' => [
                'name' => 'documents_cotutelle',
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
            'documents_coencadrement' => [
                'name' => 'documents_coencadrement',
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
        ];
    }
}