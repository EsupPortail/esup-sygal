<?php

namespace Application\Form\Rapport;

use Application\Entity\AnneeUniv;
use Application\Entity\Db\Rapport;
use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;
use These\Entity\Db\TheseAnneeUniv;

abstract class RapportForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var TheseAnneeUniv[]
     */
    protected $anneesUnivs;

    /**
     * @param TheseAnneeUniv[]|AnneeUniv[] $anneesUnivs
     * @return self
     */
    public function setAnneesUnivs(array $anneesUnivs): self
    {
        $this->anneesUnivs = $anneesUnivs;

        return $this;
    }

    protected function getAnneesUnivsAsOptions(): array
    {
        $anneesUnivs = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            if ($anneeUniv instanceof TheseAnneeUniv) {
                $anneesUnivs[$anneeUniv->getAnneeUniv()] = $anneeUniv->getAnneeUnivToString();
            } elseif ($anneeUniv instanceof AnneeUniv) {
                $anneesUnivs[$anneeUniv->getPremiereAnnee()] = (string) $anneeUniv;
            }
        }

        return $anneesUnivs;
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
                'label' => "Année universitaire <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_attributes' => [
                    'class' => 'required',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
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
            'name' => 'files',
            'type' => File::class,
            'options' => [
                'label' => "Document à joindre <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'files',
                'multiple' => true,
            ],
        ]);

        $this->add(new Csrf('security'));

        FormUtils::addUploadButton($this);

        $this->setObject(new Rapport());
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
        $anneesUnivs = $this->getAnneesUnivsAsOptions();

        /** @var Select $anneeUnivSelect */
        $anneeUnivSelect = $this->get('anneeUniv');
        $anneeUnivSelect->setValueOptions($anneesUnivs);

        if (count($anneesUnivs) === 1) {
            $anneeUnivSelect
                ->setValue(key($anneesUnivs))
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
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => array_keys($this->getAnneesUnivsAsOptions()),
                            'messages' => [
                                InArray::NOT_IN_ARRAY => "L'année universitaire n'est pas dans la liste proposée.",
                            ],
                        ],
                    ],
                ],
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
                            'extension' => ['txt', 'pdf', 'doc', 'docx', 'odt'],
                            'message' => "Le type de votre fichier n'est pas autorisé.",
                            'break_chain_on_failure' => true,
                        ],
                    ],
                    [
                        'name' => MimeType::class,
                        'options' => [
                            'mimeType' => [
                                'text/plain', // txt, csv
                                'application/pdf', // pdf
                                'application/msword', 'text/rtf', // doc
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
                                'application/vnd.oasis.opendocument.text', // odt
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