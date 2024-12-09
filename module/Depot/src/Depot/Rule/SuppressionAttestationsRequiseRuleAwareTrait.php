<?php

namespace Depot\Rule;

trait SuppressionAttestationsRequiseRuleAwareTrait
{
    protected SuppressionAttestationsRequiseRule $suppressionAttestationsRequiseRule;

    public function setSuppressionAttestationsRequiseRule(SuppressionAttestationsRequiseRule $autorisationDiffusionRule): void
    {
        $this->suppressionAttestationsRequiseRule = $autorisationDiffusionRule;
    }
}