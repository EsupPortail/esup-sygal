<?php

namespace RapportActivite\Form;

use Application\Entity\AnneeUniv;
use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\NotEmpty;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Fieldset\ActionDiffusionCultureScientifiqueFieldset;
use RapportActivite\Fieldset\AutreActiviteFieldset;
use RapportActivite\Fieldset\FormationFieldset;
use SplObjectStorage;
use These\Entity\Db\TheseAnneeUniv;
use UnicaenApp\Form\Element\Collection;

/**
 * @property \RapportActivite\Entity\Db\RapportActivite $object
 */
abstract class RapportActiviteAbstractForm extends Form implements InputFilterProviderInterface
{
    const ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE = 'annee-univ';
    const ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE_PREFIX = 'if-estFinContrat-equals-';

    const EST_FINAL__VALUE__ANNUEL = '0';
    const EST_FINAL__VALUE__FIN_CONTRAT = '1';

    protected SplObjectStorage $anneesUnivs;

    protected bool $anneesUnivsReadonly = false;

    public function setAnneesUnivs(SplObjectStorage $anneesUnivs): self
    {
        $this->anneesUnivs = $anneesUnivs;
        return $this;
    }

    public function setAnneesUnivsReadonly(bool $anneesUnivsReadonly = true): self
    {
        $this->anneesUnivsReadonly = $anneesUnivsReadonly;
        return $this;
    }

    protected function getAnneesUnivsAsOptions(): array
    {
        $valuesOptions = [];

        while ($anneeUniv = $this->anneesUnivs->current()) {
            $infos = $this->anneesUnivs->getInfo();

            if ($anneeUniv instanceof TheseAnneeUniv) {
                $data = [
                    'value' => $anneeUniv->getAnneeUniv(),
                    'label' => $anneeUniv->getAnneeUnivToString()
                ];
            } elseif ($anneeUniv instanceof AnneeUniv) {
                $data = [
                    'value' => $anneeUniv->getPremiereAnnee(),
                    'label' => (string)$anneeUniv
                ];
            }
            $data['attributes'] = $infos;
            $valuesOptions[] = $data;

            $this->anneesUnivs->next();
        }

        return $valuesOptions;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->setAttribute('method', 'post');

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'type' => Select::class,
            'name' => 'anneeUniv',
            'options' => [
                'label' => 'Année universitaire :',
                'label_attributes' => [
                    'class' => 'required',
                ],
                'empty_option' => "Sélectionner...",
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'anneeUniv',
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'descriptionProjetRecherche',
            'options' => [
                'label' => "Description du projet de recherche (7-8 lignes max) / Briefly describe (7-8 lines) the research project :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 5,
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'principauxResultatsObtenus',
            'options' => [
                'label' => "Principaux résultats obtenus (10 lignes max) / Main results to this day (up to 10 lines):",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 5,
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'productionsScientifiques',
            'options' => [
                'label' => "Productions scientifiques (articles, revues, communications, ouvrages, etc.) / Scientific output (papers, reviews, communications, published work, etc.) :",
                'label_attributes' => [
                    'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 5,
            ],
        ]);

        $formationsSpecifiques = new Collection('formationsSpecifiques');
        $formationsSpecifiques
            ->setLabel("Formations spécifiques suivies / Specific training attended : ")
            ->setMinElements(0)
            ->setOptions([
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    FormationFieldset::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($formationsSpecifiques);

        $formationsTranversales = new Collection('formationsTransversales');
        $formationsTranversales
            ->setLabel("Formations transversales suivies / Transversal training attended :")
            ->setMinElements(1)
            ->setOptions([
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    FormationFieldset::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($formationsTranversales);

        $actionsDiffusion = new Collection('actionsDiffusionCultureScientifique');
        $actionsDiffusion
            ->setLabel("Actions de diffusion de la culture scientifique, technique et industrielle (CSTI) / Actions undertaken within the framework of the promotion of scientific, technical and industrial knowledge :")
            ->setMinElements(1)
            ->setOptions([
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    ActionDiffusionCultureScientifiqueFieldset::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($actionsDiffusion);

        $autresActivites = new Collection('autresActivites');
        $autresActivites
            ->setLabel("Autres activités / Other activities :")
            ->setMinElements(0)
            ->setOptions([
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    AutreActiviteFieldset::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($autresActivites);

        $this->add([
            'type' => Textarea::class,
            'name' => 'commentaires',
            'options' => [
                'label' => "Commentaire libres (5-6 lignes max) / Additional comments (up to 5-6 lines) :",
                'label_attributes' => [
                    //'class' => 'required',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'rows' => 5,
            ],
        ]);

        $this->add(new Csrf('security'));

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Valider',
            ],
        ]);

        $this->bind(new RapportActivite());
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $this->prepareAnneeUnivSelect();

        return parent::prepare();
    }

    protected function prepareAnneeUnivSelect()
    {
        $valuesOptions = $this->getAnneesUnivsAsOptions();

        /** @var Select $anneeUnivSelect */
        $anneeUnivSelect = $this->get('anneeUniv');
        $anneeUnivSelect->setValueOptions($valuesOptions);

        if (count($valuesOptions) === 1) {
            $firstValue = reset($valuesOptions)['value'];
            $anneeUnivSelect
                ->setValue($firstValue)
                ->setEmptyOption(null);
        }

        if ($this->anneesUnivsReadonly) {
            $anneeUnivSelect->setAttribute('disabled', true);
        }
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'identifiant' => [
                'required' => false,
            ],

            'anneeUniv' => [
                'required' => !$this->anneesUnivsReadonly,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => "Veuillez indiquer l'année universitaire concernée.",
                            ],
                        ],
                    ],
//                    [
//                        'name' => InArray::class,
//                        'options' => [
////                            'haystack' => array_map(
////                                fn(array $item) => $item['value'],
////                                $this->getAnneesUnivsAsOptions()
////                            ),
//                            'messages' => [
//                                InArray::NOT_IN_ARRAY => "L'année universitaire n'est pas dans la liste proposée.",
//                            ],
//                        ],
//                    ],
                ],
            ],

            'descriptionProjetRecherche' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
            'principauxResultatsObtenus' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
            'productionsScientifiques' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],

            'commentaires' => [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ]
            ],
        ];
    }
}