<?php

namespace Depot\Rule;

use Application\Rule\RuleInterface;
use These\Entity\Db\These;
use Fichier\Entity\Db\VersionFichier;
use UnicaenApp\Exception\LogicException;

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
    private These $these;
    private VersionFichier $versionFichier;

    public function __construct(These $these, VersionFichier $versionFichier)
    {
        $this->these = $these;
        $this->versionFichier = $versionFichier;
    }

    public function execute(): bool
    {
        return $this->computeSuppressionAttestationEstRequise();
    }

    /**
     * Calcule s'il est nécessaire de supprimer les réponses aux "attestations".
     */
    private function computeSuppressionAttestationEstRequise(): bool
    {
        $attestation = $this->these->getAttestationForVersion($this->versionFichier);

        if ($attestation === null) {
            // aucune attestation remplie : suppression inutile
            return false;
        }

        $diffusion = $this->these->getDiffusionForVersion($this->versionFichier);

        if ($diffusion === null) {
            throw new LogicException("Appel de méthode prématuré : autorisation de diffusion introuvable pour la $this->versionFichier");
        }

        if ($diffusion->isRemiseExemplairePapierRequise()) {
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