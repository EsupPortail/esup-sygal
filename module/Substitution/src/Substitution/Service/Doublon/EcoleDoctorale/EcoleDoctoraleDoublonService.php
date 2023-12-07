<?php

namespace Substitution\Service\Doublon\EcoleDoctorale;

use Substitution\Service\Doublon\SpecificDoublonAbstractService;
use Substitution\Service\Doublon\Structure\StructureConcreteDoublonServiceTrait;

class EcoleDoctoraleDoublonService extends SpecificDoublonAbstractService
{
    use StructureConcreteDoublonServiceTrait;

    public function __construct()
    {
        $this->type = 'ecole_doct';
    }
}