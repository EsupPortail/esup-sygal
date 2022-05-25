<?php

namespace RapportActivite\Rule\Televersement;

trait RapportActiviteTeleversementRuleAwareTrait
{
    protected RapportActiviteTeleversementRule $rapportActiviteTeleversementRule;

    /**
     * @param \RapportActivite\Rule\Televersement\RapportActiviteTeleversementRule $rapportActiviteTeleversementRule
     */
    public function setRapportActiviteTeleversementRule(RapportActiviteTeleversementRule $rapportActiviteTeleversementRule): void
    {
        $this->rapportActiviteTeleversementRule = $rapportActiviteTeleversementRule;
    }
}