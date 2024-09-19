<?php

namespace InscriptionAdministrative\Entity\Db\Traits;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;

trait InscriptionAdministrativeAwareTrait
{
    protected InscriptionAdministrative $inscriptionAdministrative;

    public function setInscriptionAdministrative(InscriptionAdministrative $inscriptionAdministrative): void
    {
        $this->inscriptionAdministrative = $inscriptionAdministrative;
    }

}