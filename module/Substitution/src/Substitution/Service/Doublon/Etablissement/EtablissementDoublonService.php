<?php

namespace Substitution\Service\Doublon\Etablissement;

use Substitution\Service\Doublon\SpecificDoublonAbstractService;
use Substitution\Service\Doublon\Structure\StructureConcreteDoublonServiceTrait;

class EtablissementDoublonService extends SpecificDoublonAbstractService
{
    use StructureConcreteDoublonServiceTrait;

    public function __construct()
    {
        $this->type = 'etablissement';
    }
}