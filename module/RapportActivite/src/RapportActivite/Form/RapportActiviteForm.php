<?php

namespace RapportActivite\Form;

use Application\Entity\AnneeUniv;
use These\Entity\Db\TheseAnneeUniv;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;
use RapportActivite\Entity\Db\RapportActivite;
use SplObjectStorage;

class RapportActiviteForm extends Form implements InputFilterProviderInterface
{
    const ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE = 'annee-univ';
    const ANNEE_UNIV__HTML_CLASS_ATTRIB__VALUE_PREFIX = 'if-estFinal-equals-';

    const EST_FINAL__VALUE__ANNUEL = '0';
    const EST_FINAL__VALUE__FIN_CONTRAT = '1';

    protected array $estFinalValueOptions = [];

    protected SplObjectStorage $anneesUnivs;

    public function setAnneesUnivs(SplObjectStorage $anneesUnivs): self
    {
        $this->anneesUnivs = $anneesUnivs;

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

    public function addRapportAnnuelSelectOption()
    {
        $this->estFinalValueOptions[] = [
            'value' => self::EST_FINAL__VALUE__ANNUEL,
            'label' => "Rapport d'activité annuel",
        ];
    }

    public function addRapportFinContratSelectOption()
    {
        $this->estFinalValueOptions[] = [
            'value' => self::EST_FINAL__VALUE__FIN_CONTRAT,
            'label' => "Rapport d'activité de fin de contrat",
        ];
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

        $factory = $this->getFormFactory();
        $this->add($factory->create([
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
        ]));

        $this->add([
            'name' => 'estFinal',
            'type' => Radio::class,
            'options' => [
                'label' => false,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'estFinal',
            ],
        ]);

        $this->add([
            'name' => 'files',
            'type' => File::class,
            'options' => [
                'label' => "Rapport au format PDF :",
            ],
            'attributes' => [
                'id' => 'files',
                'multiple' => false,
                'accept' => '.pdf',
            ],
        ]);

        $this->add(new Csrf('security'));

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Téléverser',
            ],
        ]);

        $this->bind(new RapportActivite());
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $this->prepareEstFinalRadio();
        $this->prepareAnneeUnivSelect();

        return parent::prepare();
    }

    protected function prepareEstFinalRadio()
    {
        /** @var Radio $esFinalRadio */
        $esFinalRadio = $this->get('estFinal');
        $esFinalRadio->setValueOptions($this->estFinalValueOptions);
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
                'required' => true,
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

            'estFinal' => [
                'required' => true,
            ],

            'files' => [
                'type' => FileInput::class,
                'required' => true,
                'validators' => [
                    [
                        'name' => UploadFile::class,
                        'options' => [
                            'messages' => [
                                \Laminas\Validator\File\UploadFile::FILE_NOT_FOUND => "Veuillez fournir un fichier.",
                                \Laminas\Validator\File\UploadFile::NO_FILE => "Veuillez fournir un fichier.",
                            ],
                            'break_chain_on_failure' => true,
                        ],
                    ],
                    [
                        'name' => Extension::class,
                        'options' => [
                            'extension' => ['pdf'/*, 'txt', 'doc', 'docx', 'odt'*/],
                            'message' => "Le type de votre fichier n'est pas autorisé.",
                            'break_chain_on_failure' => true,
                        ],
                    ],
                    [
                        'name' => MimeType::class,
                        'options' => [
                            'mimeType' => [
//                                'text/plain', // txt, csv
                                'application/pdf', // pdf
//                                'application/msword', 'text/rtf', // doc
//                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
//                                'application/vnd.oasis.opendocument.text', // odt
                            ],
                            'magicFile' => false,
                            'enableHeaderCheck' => true,
                            'message' => "Le format %type% de votre fichier n'est pas autorisé.",
                            'break_chain_on_failure' => true,
                        ],
                    ],
                    [
                        'name' => Size::class,
                        'options' => [
                            'max' => '6MB',
                            'messages' => [
                                Size::TOO_BIG => "Le poids maximum autorisé pour le fichier est %max%.",
                                Size::NOT_FOUND => "Le fichier n'est pas lisible ou n'existe pas."
                            ],
                            'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}