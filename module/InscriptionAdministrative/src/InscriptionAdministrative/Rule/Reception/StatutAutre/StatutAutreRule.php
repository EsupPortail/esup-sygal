<?php

namespace InscriptionAdministrative\Rule\Reception\StatutAutre;

use InscriptionAdministrative\Rule\Reception\AbstractReceptionRule;
use InvalidArgumentException;

class StatutAutreRule extends AbstractReceptionRule
{
    /**
     * Point d'entrée.
     *
     * @see doc/integration_flux_inscrip_admin.drawio
     */
    public function execute(): void
    {
        parent::execute();

        if ($this->inscriptionAdministrative->estValidee() || $this->inscriptionAdministrative->estAnnulee()) {
            throw new InvalidArgumentException("Statut inscription inattendu");
        }

        $this->processInscriptionAutre();
    }

    private function processInscriptionAutre(): void
    {
        // ??
    }
}