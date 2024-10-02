<?php

namespace Structure\Form\InputFilter\Etablissement;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Validator\NoObjectExists;
use DoctrineModule\Validator\UniqueObject;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Uri;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Form\EtablissementForm;

class EtablissementInputFilter extends InputFilter implements EtablissementInputFilterInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->add([
            'name' => 'libelle',
            'required' => true,
        ]);
        $this->add([
            'name' => 'code',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner un code (code UAI/RNE si l'établissement est français)",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'sigle',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);
        $this->add([
            'name' => 'sourceCode',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                [
                    'name' => UniqueObject::class,
                    'priority' => -98, // pour passer en dernier
                    'options' => [
                        'object_manager' => $this->entityManager,
                        'object_repository' => $this->entityManager->getRepository(Etablissement::class),
                        'fields' => ['sourceCode'],
                        'use_context' => true,
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => "Un établissement existant possède déjà ce 'source code'",
                        ],
                    ],
                ],
                [
                    'name' => UniqueObject::class,
                    'priority' => -99, // pour passer en dernier
                    'options' => [
                        'object_manager' => $this->entityManager,
                        'object_repository' => $this->entityManager->getRepository(Structure::class),
                        'fields' => ['sourceCode'],
                        'use_context' => true,
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => "Une structure existante possède déjà ce 'source code'",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'domaine',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StringToLower::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                [
                    'name' => UniqueObject::class,
                    'priority' => -99, // pour passer en dernier
                    'options' => [
                        'object_manager' => $this->entityManager,
                        'object_repository' => $this->entityManager->getRepository(Etablissement::class),
                        'fields' => ['domaine'],
                        'use_context' => true,
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => "Un établissement existant possède déjà ce domaine",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'adresse',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'telephone',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'fax',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'email',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                ['name' => EmailAddress::class],
            ],
        ]);
        $this->add([
            'name' => 'emailAssistance',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                ['name' => EmailAddress::class],
            ],
        ]);
        $this->add([
            'name' => 'emailBibliotheque',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                ['name' => EmailAddress::class],
            ],
        ]);
        $this->add([
            'name' => 'emailDoctorat',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                ['name' => EmailAddress::class],
            ],
        ]);
        $this->add([
            'name' => 'siteWeb',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
            'validators' => [
                ['name' => Uri::class, 'options' => ['allowRelative' => false]],
            ],
        ]);
        $this->add([
            'name' => 'id_ref',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'id_hal',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'estInscription',
            'required' => false,
        ]);
        $this->add([
            'name' => 'estMembre',
            'required' => false,
        ]);
        $this->add([
            'name' => 'estAssocie',
            'required' => false,
        ]);
        $this->add([
            'name' => 'estCed',
            'required' => false,
        ]);
        $this->add([
            'name' => 'estFerme',
            'required' => false,
        ]);
        $this->add([
            'name' => 'cheminLogo',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => ToNull::class],
            ],
//                'validators' => [
//                    [
//                        'name' => Extension::class,
//                        'options' => [
//                            'extension' => ['bmp', 'png', 'jpg', 'jpeg'],
//                            'case' => false,
//                            'break_chain_on_failure' => true,
//                        ],
//                    ],
//                ],
        ]);
    }

    public function prepareForm(EtablissementForm $etablissementForm): void
    {
        $etablissementForm->remove('domaine');
        $etablissementForm->remove('emailAssistance');
        $etablissementForm->remove('emailBibliotheque');
        $etablissementForm->remove('emailDoctorat');
        $etablissementForm->remove('estInscription');
        $etablissementForm->remove('estCed');
        if (!$etablissementForm->getObject()->getId()) {
            $etablissementForm->remove('estFerme');
        }

        if ($etablissementForm->getObject()->getId()) {
            $etablissementForm->get('sourceCode')->setAttribute('disabled', 'disabled');
        }
    }
}