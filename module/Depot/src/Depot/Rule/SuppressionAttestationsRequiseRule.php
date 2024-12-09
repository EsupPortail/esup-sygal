<?php

namespace Depot\Rule;

use Application\Rule\AbstractRule;
use Fichier\Entity\Db\VersionFichier;
use These\Entity\Db\These;
use UnicaenApp\Exception\LogicException;

/**
 * Règle déterminant s'il est nécessaire de supprimer les réponses aux "attestations" selon les réponses
 * à l'autorisation de diffusion.
 *
 * Supprimer les réponses aux attestations permet de redemander à l'utilisateur d'y répondre à nouveau.
 */
class SuppressionAttestationsRequiseRule extends AbstractRule
{
    use RemiseExemplairePapierRuleAwareTrait;

    private These $these;
    private VersionFichier $versionFichier;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($options['these'])) {
            $this->setThese($options['these']);
        }
        if (isset($options['versionFichier'])) {
            $this->setVersionFichier($options['versionFichier']);
        }
    }

    public function setThese(These $these): static
    {
        $this->these = $these;
        return $this;
    }

    public function setVersionFichier(VersionFichier $versionFichier): static
    {
        $this->versionFichier = $versionFichier;
        return $this;
    }

    public function execute(): static
    {
        return $this;
    }

    /**
     * Calcule s'il est nécessaire de supprimer les réponses aux "attestations".
     */
    public function isSuppressionAttestationRequired(): bool
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

        // d'après la réponse à l'autorisation de MEL, la remise d'un exemplaire papier est-elle requise ?
        $this->remiseExemplairePapierRule->setDiffusion($diffusion);
        $dateObservation = $diffusion->getHistoModification() ?: $diffusion->getHistoCreation();
        $remiseExemplairePapierEstRequise = $this->remiseExemplairePapierRule->isRemiseExemplairePapierRequired($dateObservation);

        if ($remiseExemplairePapierEstRequise) {
            if (! $attestation->getExemplaireImprimeConformeAVersionDeposee()) {
                // la question "exemplaire papier conforme" n'avait pas été posée (null ou false), il faudra la poser :
                // suppression de l'attestation
                return true;
            }
        } else {
            if ($attestation->getExemplaireImprimeConformeAVersionDeposee()) {
                // la question "exemplaire papier conforme" avait été posée (true), ce n'est plus pertinent :
                // suppression de l'attestation
                return true;
            }
        }

        // sinon, pas de suppression de l'attetestation
        return false;
    }
}