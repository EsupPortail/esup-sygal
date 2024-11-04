<?php

namespace Structure\Form\InputFilter;

use Doctrine\ORM\EntityManager;
use Laminas\Form\Form;

interface StructureConcreteInputFilterInterface
{
    public function __construct(EntityManager $entityManager);
    public function prepareForm(Form $structureConcreteForm): void;
}