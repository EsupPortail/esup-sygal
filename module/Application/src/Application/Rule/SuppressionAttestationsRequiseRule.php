<?php

namespace Application\Rule;

use Application\Entity\Db\These;

/**
 * Règle déterminant s'il est nécessaire de supprimer les réponses aux "attestations" selon les réponses
 * à l'autorisation de diffusion.
 *
 * Supprimer les réponses aux attestations permet de redemander à l'utilisateur d'y répondre à nouveau.
 *
 * @author Unicaen
 */
class SuppressionAttestationsRequiseRule implements RuleInterface
{
    /**
     * @var These
     */
    private $these;

    /**
     * @var bool
     */
    private $estRequise;

    /**
     * @param These $these
     * @return self
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * @return self
     */
    public function execute()
    {
        // rien n'est fait ici

        return $this;
    }

    /**
     * Retourne un booléen indiquant si la remise d'un exemplaire papier est requise.
     *
     * @return bool
     */
    public function computeEstRequise(): bool
    {
        $attestation = $this->these->getAttestation();

        if ($attestation === null) {
            // aucune attestation remplie : suppression inutile
            return false;
        }

        $rule = new AutorisationDiffusionRule();
        $rule->setDiffusion($this->these->getDiffusion());
        $rule->execute();
        $remisePapierRequise = $rule->computeRemiseExemplairePapierEstRequise();

        if ($remisePapierRequise) {
            if (! $attestation->getExemplaireImprimeConformeAVersionDeposee()) {
                // la question "exemplaire papier conforme" n'a pas été posée, il faudra la poser : suppression
                return true;
            }
        } else {
            if ($attestation->getExemplaireImprimeConformeAVersionDeposee()) {
                // la question "exemplaire papier conforme" a été posée, ce n'est plus pertinent : suppression
                return true;
            }
        }

        return false;
    }
}