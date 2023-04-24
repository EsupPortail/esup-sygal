<?php

namespace RapportActivite\Rule\Creation;

trait RapportActiviteCreationRuleAwareTrait
{
    protected RapportActiviteCreationRule $rapportActiviteCreationRule;

    /**
     * @param \RapportActivite\Rule\Creation\RapportActiviteCreationRule $rapportActiviteCreationRule
     */
    public function setRapportActiviteCreationRule(RapportActiviteCreationRule $rapportActiviteCreationRule): void
    {
        $this->rapportActiviteCreationRule = $rapportActiviteCreationRule;
    }
}