<?php

namespace Application\Form;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Service\FichierThese\FichierTheseServiceAwareInterface;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Application\Service\Message\DiffusionMessages;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Element\Date;
use UnicaenApp\Message\MessageServiceAwareInterface;
use UnicaenApp\Message\MessageServiceAwareTrait;
use Zend\Form\Form;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Callback;

class DiffusionTheseForm extends Form
    implements InputFilterProviderInterface, MessageServiceAwareInterface, FichierTheseServiceAwareInterface
{
    use MessageServiceAwareTrait;
    use FichierTheseServiceAwareTrait;

    /**
     * @var VersionFichier
     */
    protected $versionFichier;

    /**
     * @param VersionFichier $versionFichier
     * @return DiffusionTheseForm
     */
    public function setVersionFichier(VersionFichier $versionFichier)
    {
        $this->versionFichier = $versionFichier;

        return $this;
    }

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
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
            'type'       => 'Radio',
            'name'       => 'droitAuteurOk',
            'options'    => [
                'label'              => $this->messageService->render(DiffusionMessages::DROITS_AUTEUR_OK_FORM_LABEL),
                'value_options'      => [
                    [
                        'value'      => $value = Diffusion::DROIT_AUTEUR_OK_OUI,
                        'label'      => $this->messageService->render(DiffusionMessages::DROITS_AUTEUR_OK_FORM_VALUE, [], $value),
                        'attributes' => [
                            'id'    => 'oui',
                            'class' => 'droitAuteur',
                        ],
                    ],
                    [
                        'value'      => $value = Diffusion::DROIT_AUTEUR_OK_NON,
                        'label'      => $this->messageService->render(DiffusionMessages::DROITS_AUTEUR_OK_FORM_VALUE, [], $value),
                        'attributes' => [
                            'id'    => 'non',
                            'class' => 'droitAuteur',
                        ],
                    ],
                ],
                'use_hidden_element' => true,
                'unchecked_value'    => '',
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
            'type'       => 'Radio',
            'name'       => 'autorisMel',
            'options'    => [
                'label'              => "(initialisé lors du bind)",
                'value_options'      => [
                    [
                        'value'      => $value = Diffusion::AUTORISATION_OUI_IMMEDIAT,
                        'label'      => $this->messageService->render(DiffusionMessages::AUTORIS_DIFFUSION_FORM_VALUE, [], $value),
                        'attributes' => [
                            'id'    => 'ouiImmediat',
                            'class' => 'autoris',
                        ],
                    ],
                    [
                        'value'      => $value = Diffusion::AUTORISATION_OUI_EMBARGO,
                        'label'      => $this->messageService->render(DiffusionMessages::AUTORIS_DIFFUSION_FORM_VALUE, [], $value),
                        'attributes' => [
                            'id'    => 'ouiEmbargo',
                            'class' => 'autoris',
                        ],
                    ],
                    [
                        'value'      => $value = Diffusion::AUTORISATION_NON,
                        'label'      => $this->messageService->render(DiffusionMessages::AUTORIS_DIFFUSION_FORM_VALUE, [], $value),
                        'attributes' => [
                            'id'    => 'non',
                            'class' => 'autoris',
                        ],
                    ],
                ],
                'use_hidden_element' => true,
                'unchecked_value'    => '',
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
            'type'       => 'Text',
            'name'       => 'idOrcid',
            'options'    => [
                'label' => 'Identifiant ORCID (facultatif)',
            ],
            'attributes' => [
                'title' => "",
            ],
        ]);

        $this->add([
            'type'       => 'Checkbox',
            'name'       => 'certifCharteDiff',
            'options'    => [
                'label'              => "L'auteur certifie avoir pris connaissance de la charte de dépôt et de diffusion des thèses de Normandie Université",
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

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    /**
     * @param Diffusion $diffusion
     * @param int    $flags
     * @return static
     */
    public function bind($diffusion, $flags = FormInterface::VALUES_NORMALIZED)
    {
        parent::bind($diffusion, $flags);

        /**
         * Le label de l'autorisation de diffusion dépnd de la confidentialité.
         */
        $label = $this->messageService->render(DiffusionMessages::AUTORIS_DIFFUSION_FORM_LABEL, [], $this->get('confidentielle')->getValue());
        $this->get('autorisMel')->setLabel($label);

        return $this;
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $required = [];

        /**
         * Autorisation de mise en ligne
         */
        $autorisMel = $this->get('autorisMel')->getValue();
        switch ($autorisMel) {
            case null:
                $required['autorisEmbargoDuree'] = false;
                $required['autorisMotif'] = false;
                break;
            case Diffusion::AUTORISATION_OUI_IMMEDIAT:
                $required['autorisEmbargoDuree'] = false;
                $required['autorisMotif'] = false;
                break;
            case Diffusion::AUTORISATION_OUI_EMBARGO:
                $required['autorisEmbargoDuree'] = true;
                $required['autorisMotif'] = true;
                break;
            case Diffusion::AUTORISATION_NON:
                $required['autorisEmbargoDuree'] = false;
                $required['autorisMotif'] = true;
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
            $name = 'idOrcid'        => [
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