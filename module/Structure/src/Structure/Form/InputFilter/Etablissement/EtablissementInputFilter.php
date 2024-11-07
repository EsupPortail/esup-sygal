<?php

namespace Structure\Form\InputFilter\Etablissement;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Validator\UniqueObject;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Structure\Entity\Db\Etablissement;
use Structure\Form\InputFilter\StructureInputFilter;
use Structure\Form\StructureForm;

class EtablissementInputFilter extends StructureInputFilter
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);

        $this->add([
            'name' => 'code',
            'required' => true,
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
        ]);
        $this->add([
            'name' => 'sourceCode',
            'required' => false,
            'validators' => [
                [
                    'name' => UniqueObject::class,
                    'priority' => -97, // pour passer juste avant celui déclaré dans la classe mère
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
            'name' => 'estInscription',
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
    }

    public function prepareForm(StructureForm $structureForm): void
    {
        parent::prepareForm($structureForm);

        $structureForm->remove('domaine');
        $structureForm->remove('emailAssistance');
        $structureForm->remove('emailBibliotheque');
        $structureForm->remove('emailDoctorat');
        $structureForm->remove('estInscription');
        $structureForm->remove('estCed');
    }
}