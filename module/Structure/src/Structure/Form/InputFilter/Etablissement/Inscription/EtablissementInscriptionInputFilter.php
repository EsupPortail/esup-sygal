<?php

namespace Structure\Form\InputFilter\Etablissement\Inscription;

use Doctrine\ORM\EntityManager;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Regex;
use Structure\Form\EtablissementForm;
use Structure\Form\InputFilter\Etablissement\EtablissementInputFilter;
use Structure\Form\InputFilter\Etablissement\EtablissementInputFilterInterface;
use Webmozart\Assert\Assert;

class EtablissementInscriptionInputFilter extends EtablissementInputFilter implements EtablissementInputFilterInterface
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);

        $this->add([
            'name' => 'code',
            'required' => true,
            'validators' => [
                [
                    'name' => Regex::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'pattern' => '/^\d{7}[A-Z]$/',  // 7 chiffres et 1 lettre majuscule
                        'messages' => [
                            Regex::NOT_MATCH => "Le code UAI/RNE doit être composé de 7 chiffres et 1 lettre majuscule",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'sigle',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner le sigle",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'sourceCode',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner le 'source code'",
                        ],
                    ],
                ],
                [
                    'name' => Regex::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'pattern' => '/^[A-Z]{2,}$/',
                        'messages' => [
                            Regex::NOT_MATCH => "Le 'source code' doit être composé de lettres majuscules (2 au moins) et être unique parmi les structures",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'domaine',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'priority' => 0,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner le domaine",
                        ],
                    ],
                ]
            ],
        ]);
        $this->add([
            'name' => 'adresse',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner l'adresse postale",
                        ],
                    ],
                ]
            ],
        ]);
        $this->add([
            'name' => 'telephone',
            'required' => false,
        ]);
        $this->add([
            'name' => 'fax',
            'required' => false,
        ]);
        $this->add([
            'name' => 'email',
            'required' => false,
        ]);
        $this->add([
            'name' => 'emailAssistance',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'priority' => 99,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner l'adresse électronique d'assistance",
                        ],
                    ],
                ],

            ],
        ]);
        $this->add([
            'name' => 'emailBibliotheque',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'priority' => 99,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner l'adresse électronique pour les aspects Bibliothèque",
                        ],
                    ],
                ],

            ],
        ]);
        $this->add([
            'name' => 'emailDoctorat',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'priority' => 99,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner l'adresse électronique pour les aspects Doctorat",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'siteWeb',
            'required' => false,
        ]);
        $this->add([
            'name' => 'id_ref',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner l'IdRef",
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function prepareForm(EtablissementForm $etablissementForm): void
    {
        Assert::true($etablissementForm->getObject()->estInscription(), "L'établissement bindé n'est pas valide");

        $etablissementForm->get('code')->setLabel("Code UAI (RNE)");
        $etablissementForm->get('sourceCode')->setAttribute('placeholder', "Ex : UCN");
        $etablissementForm->get('estInscription')->setAttribute('disabled', 'disabled');
        $etablissementForm->remove('estMembre');
        $etablissementForm->remove('estAssocie');
        $etablissementForm->remove('estCed');
        $etablissementForm->remove('estFerme');

        if ($etablissementForm->getObject()->getId()) {
            $etablissementForm->get('sourceCode')->setAttribute('disabled', 'disabled');
        }
    }
}