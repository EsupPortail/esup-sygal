<?php

namespace InscriptionAdministrative\Rule\Reception\StatutAnnulee;

use InscriptionAdministrative\Rule\Reception\AbstractReceptionRule;
use InvalidArgumentException;

class StatutAnnuleeRule extends AbstractReceptionRule
{
    /**
     * Point d'entrée.
     *
     * @see doc/integration_flux_inscrip_admin.drawio
     */
    public function execute(): void
    {
        parent::execute();

        if (!$this->inscriptionAdministrative->estAnnulee()) {
            throw new InvalidArgumentException("Statut inscription inattendu");
        }

        $this->processInscriptionAnnulee();
    }

    private function processInscriptionAnnulee(): void
    {
        // ??
    }
}