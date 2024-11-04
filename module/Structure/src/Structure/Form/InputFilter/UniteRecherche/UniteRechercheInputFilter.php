<?php

namespace Structure\Form\InputFilter\UniteRecherche;

use Doctrine\ORM\EntityManager;
use Laminas\Filter\ToNull;
use Laminas\Validator\NotEmpty;
use Structure\Form\InputFilter\StructureInputFilter;
use Structure\Form\InputFilter\StructureInputFilterInterface;

class UniteRechercheInputFilter extends StructureInputFilter implements StructureInputFilterInterface
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);

        $this->add([
            'name' => 'sigle',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner le code de l'unité de recherche, ex : HisTeMé",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'code',
            'required' => true, // requis pour le calcul du nom de fichier logo
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            'isEmpty' => "Vous devez renseigner le code de l'unité de recherche, ex : UR7455",
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'id_ref',
            'required' => true,
        ]);
        $this->add([
            'name' => 'RNSR',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'cheminLogo',
            'required' => true,
        ]);
    }
}