<?php

namespace Structure\Form\InputFilter;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Validator\UniqueObject;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\Uri;
use Structure\Entity\Db\Structure;
use Structure\Form\StructureForm;

class StructureInputFilter extends InputFilter implements StructureInputFilterInterface
{
    protected EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->add([
            'name' => 'libelle',
            'required' => true,
        ]);
        $this->add([
            'name' => 'code',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
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
            'validators' => [
                [
                    'name' => UploadFile::class,
                ],
                [
                    'name' => Extension::class,
                    'options' => [
                        'extension' => $exts = ['bmp', 'png', 'jpg', 'jpeg'],
                        'case' => false,
                        'break_chain_on_failure' => true,
                        'messages' => [
                            Extension::FALSE_EXTENSION =>
                                "Les seuls types d'image acceptés sont les suivants : " . implode(', ', $exts),
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function prepareForm(StructureForm $structureForm): void
    {
        if (!$structureForm->getObject()->getId() && $structureForm->has('estFerme')) {
            $structureForm->remove('estFerme');
        }

        if ($structureForm->getObject()->getId() && $structureForm->has('sourceCode')) {
            $structureForm->get('sourceCode')->setAttribute('disabled', 'disabled');
        }
    }
}