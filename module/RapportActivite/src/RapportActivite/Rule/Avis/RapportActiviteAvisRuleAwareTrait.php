<?php

namespace RapportActivite\Rule\Avis;

/**
 * @deprecated
 */
trait RapportActiviteAvisRuleAwareTrait
{
    /**
     * @var \RapportActivite\Rule\Avis\RapportActiviteAvisRule
     * @deprecated
     */
    protected RapportActiviteAvisRule $rapportActiviteAvisRule;

    /**
     * @param \RapportActivite\Rule\Avis\RapportActiviteAvisRule $rapportActiviteAvisRule
     */
    public function setRapportActiviteAvisRule(RapportActiviteAvisRule $rapportActiviteAvisRule): void
    {
        $this->rapportActiviteAvisRule = $rapportActiviteAvisRule;
    }
}