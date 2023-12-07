<?php

namespace Substitution\Service\Doublon\UniteRecherche;

use Substitution\Service\Doublon\SpecificDoublonAbstractService;
use Substitution\Service\Doublon\Structure\StructureConcreteDoublonServiceTrait;

class UniteRechercheDoublonService extends SpecificDoublonAbstractService
{
    use StructureConcreteDoublonServiceTrait;

    public function __construct()
    {
        $this->type = 'unite_rech';
    }
}