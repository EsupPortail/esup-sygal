<?php

namespace Structure\Form\InputFilter\EcoleDoctorale;

use Doctrine\ORM\EntityManager;
use Laminas\Filter\ToNull;
use Laminas\Validator\Uri;
use Structure\Form\InputFilter\StructureInputFilterInterface;
use Structure\Form\InputFilter\StructureInputFilter;

class EcoleDoctoraleInputFilter extends StructureInputFilter implements StructureInputFilterInterface
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);

        $this->add([
            'name' => 'sigle',
            'required' => true,
        ]);
        $this->add([
            'name' => 'code',
            'required' => true, // requis pour le calcul du nom de fichier logo
        ]);
        $this->add([
            'name' => 'id_ref',
            'required' => true,
        ]);
        $this->add([
            'name' => 'id_hal',
            'required' => false,
        ]);
        $this->add([
            'name' => 'theme',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => ToNull::class],
            ],
        ]);
        $this->add([
            'name' => 'offre-these',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => ToNull::class],
            ],
            'validators' => [
                ['name' => Uri::class, 'options' => ['allowRelative' => false]],
            ],
        ]);
        $this->add([
            'name' => 'cheminLogo',
            'required' => true,
        ]);
    }
}