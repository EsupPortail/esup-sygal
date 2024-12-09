<?php

namespace Depot\Form\Diffusion;

use Application\Utils\FormUtils;
use Depot\Entity\Db\Diffusion;
use Depot\Service\FichierThese\FichierTheseServiceAwareInterface;
use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Element\Date;
use UnicaenApp\Message\MessageServiceAwareInterface;
use UnicaenApp\Message\MessageServiceAwareTrait;

class DiffusionTheseForm extends Form
    implements InputFilterProviderInterface, MessageServiceAwareInterface, FichierTheseServiceAwareInterface
{
    use MessageServiceAwareTrait;
    use FichierTheseServiceAwareTrait;

    protected VersionFichier $versionFichier;

    public function setVersionFichier(VersionFichier $versionFichier): static
    {
        $this->versionFichier = $versionFichier;

        return $this;
    }

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init(): void
    {
        $this->setObject(new Diffusion());

        $this->add([
            'type'       => 'Radio',
            'name'       => 'confidentielle',
            'options'    => [
                'label'              => "Confidentialité de la thèse",
                'value_options'      => [
                    [
                        'value'      => Diffusion::CONFIDENTIELLE_OUI,
                        'label'      => "Oui.",
                        'attributes' => [
                            'id'    => 'oui',
                            'class' => 'confident',
                        ],
                    ],
                    [
                        'value'      => Diffusion::CONFIDENTIELLE_NON,
                        'label'      => "Non.",
                        'attributes' => [
                            'id'    => 'non',
                            'class' => 'confident',
                        ],
                    ],
                ],
                'use_hidden_element' => true,
                'unchecked_value'    => '',
            ],
            'attributes' => [
                'title'    => "",
                'disabled' => true,
            ],
        ]);

        $this->add([
            'type'       => Date::class,
            'name'       => 'dateFinConfidentialite',
            'options'    => [
                'label' => 'Date de fin de confidentialité',
            ],
            'attributes' => [
                'title' => "",
                'disabled' => true,
            ],
        ]);

        $this->add([
            'type' => 'Radio',
            'name' => 'droitAuteurOk',
            'options' => [
                'label' => "L'auteur garantit que tous les documents de la thèse sont libres de droits " .
                    "ou qu'il a les droits afférents pour la reproduction et la représentation sur tous supports",
                'value_options' => [
                    [
                        'value' => Diffusion::DROIT_AUTEUR_OK_OUI,
                        'label' => "Oui",
                        'attributes' => [
                            'id' => 'oui',
                            'class' => 'droitAuteur',
                        ],
                    ],
                    [
                        'value' => Diffusion::DROIT_AUTEUR_OK_NON,
                        'label' => "Non, à défaut il fournit une version numérique spécifique excluant ces oeuvres tierces (version de diffusion)...",
                        'attributes' => [
                            'id' => 'non',
                            'class' => 'droitAuteur',
                        ],
                    ],
                ],
                'use_hidden_element' => true,
                'unchecked_value' => '',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

//        $this->add([
//            'type'       => 'Checkbox',
//            'name'       => 'certifConformMel',
//            'options'    => [
//                'label'              => "L'auteur certifie que l'exemplaire imprimé déposé conjointement est conforme à la version mise en ligne",
//                'use_hidden_element' => true,
//                'unchecked_value'    => '' // indispensable car validateur NotEmpty
//            ],
//            'attributes' => [
//                'title' => "",
//            ],
//        ]);

        $this->add([
            'type' => 'Radio',
            'name' => 'autorisMel',
            'options' => [
                'label' => "(initialisé lors du bind)",
                'value_options' => [
                    [
                        'value' => Diffusion::AUTORISATION_OUI_IMMEDIAT,
                        'label' => "Oui, immédiatement",
                        'attributes' => [
                            'id' => 'ouiImmediat',
                            'class' => 'autoris',
                        ],
                    ],
                    [
                        'value' => Diffusion::AUTORISATION_OUI_EMBARGO,
                        'label' => "Oui, avec embargo après soutenance d'une durée de...",
                        'attributes' => [
                            'id' => 'ouiEmbargo',
                            'class' => 'autoris',
                        ],
                    ],
                    [
                        'value' => Diffusion::AUTORISATION_NON,
                        'label' => "Non",
                        'attributes' => [
                            'id' => 'non',
                            'class' => 'autoris',
                        ],
                    ],
                ],
                'use_hidden_element' => true,
                'unchecked_value' => '',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Radio',
            'name'       => 'autorisEmbargoDuree',
            'options'    => [
                'label'              => "Durée de l'embargo",
                'value_options'      => [
                    $k = Diffusion::EMBARGO_DUREE_6_MOIS => $k,
                    $k = Diffusion::EMBARGO_DUREE_1_AN   => $k,
                    $k = Diffusion::EMBARGO_DUREE_2_ANS  => $k,
                    $k = Diffusion::EMBARGO_DUREE_5_ANS  => $k,
                ],
                'use_hidden_element' => true,
                'unchecked_value'    => '',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Textarea',
            'name'       => 'autorisMotif',
            'options'    => [
                'label' => 'Motif',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type' => 'Radio',
            'name' => 'autorisPretPapier',
            'options' => [
                'label' => "J'autorise le prêt du fichier papier de ma thèse dans les établissements d'enseignement supérieur en dehors de la France :",
                'value_options' => [
                    Diffusion::AUTORISATION_PRET_PAPIER_OUI =>
                        "Oui (en cliquant sur Oui, vous acceptez également que la BU imprime un exemplaire papier pour permettre le prêt)",
                    Diffusion::AUTORISATION_PRET_PAPIER_NON =>
                        "Non",
                ],
                'use_hidden_element' => true,
                'unchecked_value' => '',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Text',
            'name'       => 'orcid',
            'options'    => [
                'label' => 'Identifiant ORCID (facultatif)',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Text',
            'name'       => 'halId',
            'options'    => [
                'label' => 'IdHAL (facultatif)',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Checkbox',
            'name'       => 'certifCharteDiff',
            'options'    => [
                'label'              => "L'auteur certifie avoir pris connaissance de la charte de dépôt et de diffusion des thèses",
                'use_hidden_element' => true,
                'unchecked_value'    => '' // indispensable car validateur NotEmpty
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'name' => 'id',
            'type' => 'Hidden',
        ]);

        $this->add([
            'name' => 'these',
            'type' => 'Hidden',
        ]);

        FormUtils::addSaveButton($this);
    }

    /**
     * @param Diffusion $object
     * @param int $flags
     * @return static
     */
    public function bind($object, int $flags = FormInterface::VALUES_NORMALIZED): static
    {
        parent::bind($object, $flags);

        /**
         * Le label de l'autorisation de diffusion dépnd de la confidentialité.
         */
        $labels = [
            Diffusion::CONFIDENTIELLE_NON => "L'auteur autorise la diffusion de sa thèse",
            Diffusion::CONFIDENTIELLE_OUI => "L'auteur autorise la diffusion de sa thèse à l'issue de la période de confidentialité",
        ];
        $this->get('autorisMel')->setLabel($labels[$this->get('confidentielle')->getValue()]);

        return $this;
    }

    public function getInputFilterSpecification(): array
    {
        $required = [];

        /**
         * Autorisation de mise en ligne
         */
        $autorisMel = $this->get('autorisMel')->getValue();
        switch ($autorisMel) {
            case null:
            case Diffusion::AUTORISATION_OUI_IMMEDIAT:
                $required['autorisEmbargoDuree'] = false;
                $required['autorisMotif'] = false;
                $required['autorisPretPapier'] = false;
                break;
            case Diffusion::AUTORISATION_OUI_EMBARGO:
                $required['autorisEmbargoDuree'] = true;
                $required['autorisMotif'] = true;
                $required['autorisPretPapier'] = true;
                break;
            case Diffusion::AUTORISATION_NON:
                $required['autorisEmbargoDuree'] = false;
                $required['autorisMotif'] = true;
                $required['autorisPretPapier'] = true;
                break;
        }

        /**
         * Confidentialité
         */
        $confident = $this->get('confidentielle');
        if ($confident->getAttribute('disabled')) {
            $required['confidentielle'] = false;
            $required['dateFinConfidentialite'] = false;
        } else {
            $required['confident'] = true;
            switch ($confident->getValue()) {
                case null:
                    $required['dateFinConfidentialite'] = false;
                    break;
                case Diffusion::CONFIDENTIELLE_OUI:
                    $required['dateFinConfidentialite'] = true;
                    break;
                case Diffusion::CONFIDENTIELLE_NON:
                    $required['dateFinConfidentialite'] = false;
                    break;
            }
        }

        return [
            $name = 'confidentielle'           => [
                'required'   => $required[$name],
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez répondre à la question"],
                        ],
                    ],
                ],
            ],
            $name = 'dateFinConfidentialite'    => [
                'required'   => $required[$name],
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez renseigner la date de fin de confidentialité"],
                        ],
                    ],
                ],
            ],
            $name = 'droitAuteurOk'         => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez répondre à la question"],
                        ],
                    ],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => function($value) {
                                if ($this->versionFichier === null) {
                                    throw new LogicException("Une VersionFichier est requise par le validateur droitAuteurOk");
                                }
                                /** @var Diffusion $diffusion */
                                $diffusion = $this->getObject();
                                $besoinVersionExpurgee = $value === Diffusion::DROIT_AUTEUR_OK_NON;
                                $theseExpurgeeDeposee = ! empty($this->fichierTheseService->getRepository()->fetchFichierTheses($diffusion->getThese(), NatureFichier::CODE_THESE_PDF, $this->versionFichier, false));

                                // des fichiers expurgés doivent avoir été déposés en cas de pb de droit d'auteur
                                if ($besoinVersionExpurgee && !$theseExpurgeeDeposee) {
                                    return false;
                                }
                                return true;
                            },
                            'messages' => [
                                Callback::INVALID_VALUE => "Conformément à votre réponse, vous devez téléverser ci-après une version expurgée pour la diffusion"
                            ],
                        ]
                    ]
                ],
            ],
//            $name = 'certifConformMel'       => [
//                'required'   => true,
//                'validators' => [
//                    [
//                        'name'    => 'NotEmpty',
//                        'options' => [
//                            'messages' => ['isEmpty' => "Vous devez cocher la case"],
//                        ],
//                    ],
//                ],
//            ],
            $name = 'autorisMel'          => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez répondre à la question"],
                        ],
                    ],
                ],
            ],
            $name = 'autorisEmbargoDuree' => [
                'required'   => $required[$name],
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez spécifier une durée d'embargo"],
                        ],
                    ],
                ],
            ],
            $name = 'autorisMotif'        => [
                'required'   => $required[$name],
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez renseigner le motif"],
                        ],
                    ],
                ],
            ],
            $name = 'autorisPretPapier' => [
                'required' => $required[$name],
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez répondre à cette question"],
                        ],
                    ],
                ],
            ],
            $name = 'orcid'        => [
                'required'   => false,
            ],
            $name = 'certifCharteDiff'   => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty' => "Vous devez cocher la case"],
                        ],
                    ],
                ],
            ],
        ];
    }
}