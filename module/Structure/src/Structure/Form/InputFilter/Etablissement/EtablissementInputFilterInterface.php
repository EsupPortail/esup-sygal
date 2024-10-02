<?php

namespace Structure\Form\InputFilter\Etablissement;

use Doctrine\ORM\EntityManager;
use Structure\Form\EtablissementForm;

interface EtablissementInputFilterInterface
{
    public function __construct(EntityManager $entityManager);
    public function prepareForm(EtablissementForm $etablissementForm): void;
}