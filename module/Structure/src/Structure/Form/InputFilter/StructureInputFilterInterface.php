<?php

namespace Structure\Form\InputFilter;

use Doctrine\ORM\EntityManager;
use Structure\Form\StructureForm;

interface StructureInputFilterInterface
{
    public function __construct(EntityManager $entityManager);
    public function prepareForm(StructureForm $structureForm): void;
}