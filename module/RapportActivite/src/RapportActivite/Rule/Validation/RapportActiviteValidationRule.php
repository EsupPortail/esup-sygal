<?php

namespace RapportActivite\Rule\Validation;

use Application\Rule\RuleInterface;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * Règles métiers concernant la validation des rapports d'activité.
 */
class RapportActiviteValidationRule implements RuleInterface
{
    use RapportActiviteAvisServiceAwareTrait;
    use MessageAwareTrait;

    private RapportActivite $rapportActivite;
    private bool $validationPossible;

    /**
     * Spécifie le rapport d'activité concerné.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @return self
     */
    public function setRapportActivite(RapportActivite $rapportActivite): self
    {
        $this->rapportActivite = $rapportActivite;

        return $this;
    }

    /**
     * @inheritDoc
     * @return self
     */
    public function execute(): self
    {
        $this->validationPossible = false;

        // recherche du dernier avis apporté
        $mostRecentRapportActiviteAvis =
            $this->rapportActiviteAvisService->findMostRecentRapportAvisForRapport($this->rapportActivite);
        if ($mostRecentRapportActiviteAvis === null) {
            return $this;
        }

        // recherche du type d'avis attendu ensuite
        $nextAvisTypeForRapport =
            $this->rapportActiviteAvisService->findNextExpectedAvisTypeForRapport($this->rapportActivite);

        // La validation est possible à condition que le dernier avis apporté soit positif ou négatif
        // et qu'aucun type d'avis ne soit attendu ensuite.
        $this->validationPossible =
            in_array($mostRecentRapportActiviteAvis->getAvis()->getAvisValeur()->getCode(), [
                    RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF,
                    RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF]) &&
            null === $nextAvisTypeForRapport;

        return $this;
    }

    /**
     * Indique si les conditions sont réunies pour la validation du rapport d'activité.
     *
     * @return bool
     */
    public function isValidationPossible(): bool
    {
        return $this->validationPossible;
    }
}