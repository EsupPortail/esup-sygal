<?php

namespace RapportActivite\Rule\Validation;

trait RapportActiviteValidationRuleAwareTrait
{
    protected RapportActiviteValidationRule $rapportActiviteValidationRule;

    /**
     * @param \RapportActivite\Rule\Validation\RapportActiviteValidationRule $rapportActiviteValidationRule
     */
    public function setRapportActiviteValidationRule(RapportActiviteValidationRule $rapportActiviteValidationRule): void
    {
        $this->rapportActiviteValidationRule = $rapportActiviteValidationRule;
    }
}