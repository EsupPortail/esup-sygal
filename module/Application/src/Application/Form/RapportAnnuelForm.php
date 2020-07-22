<?php

namespace Application\Form;

use Application\Entity\Db\RapportAnnuel;
use Application\Entity\Db\TheseAnneeUniv;
use Application\Form\Hydrator\RapportAnnuelHydrator;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\File;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\File\Extension;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\Size;
use Zend\Validator\File\UploadFile;
use Zend\Validator\NotEmpty;

class RapportAnnuelForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var TheseAnneeUniv[]
     */
    private $theseAnneeUnivs;

    /**
     * @param array $theseAnneeUnivs
     * @return self
     */
    public function setTheseAnneeUnivs(array $theseAnneeUnivs)
    {
        $this->theseAnneeUnivs = $theseAnneeUnivs;

        return $this;
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
            'name' => 'files',
            'type' => File::class,
            'options' => [
                'label' => "Document à joindre :",
            ],
            'attributes' => [
                'id' => 'files',
                'multiple' => true,
            ],
        ]);

        $this->add([
            'name' => 'estFinal',
            'type' => Checkbox::class,
            'options' => [
                'label' => "Il s'agit du rapport de fin de thèse.",
            ],
            'attributes' => [
                'id' => 'estFinal',
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

        $this->bind(new RapportAnnuel());
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
        $anneesUnivs = [];
        foreach ($this->theseAnneeUnivs as $tau) {
            /** @var TheseAnneeUniv $tau */
            $anneesUnivs[$tau->getAnneeUniv()] = $tau->getAnneeUnivToString();
        }
        /** @var Select $anneeUnivSelect */
        $anneeUnivSelect = $this->get('anneeUniv');
        $anneeUnivSelect->setValueOptions($anneesUnivs);
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
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
                                \Zend\Validator\File\UploadFile::FILE_NOT_FOUND => "Veuillez fournir un fichier.",
                                \Zend\Validator\File\UploadFile::NO_FILE => "Veuillez fournir un fichier.",
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

            'estFinal' => [
                'required' => false,
            ],
        ];
    }
}