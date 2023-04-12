<?php

namespace RapportActivite\Rule\Operation;

trait RapportActiviteOperationRuleAwareTrait
{
    protected RapportActiviteOperationRule $rapportActiviteOperationRule;

    /**
     * @param \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule
     */
    public function setRapportActiviteOperationRule(RapportActiviteOperationRule $rapportActiviteOperationRule): void
    {
        $this->rapportActiviteOperationRule = $rapportActiviteOperationRule;
    }
}