<?php

namespace RapportActivite\Rule\Avis;

trait RapportActiviteNotificationRuleAwareTrait
{
    protected RapportActiviteAvisNotificationRule $rapportActiviteAvisNotificationRule;

    /**
     * @param \RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRule $rapportActiviteAvisNotificationRule
     */
    public function setRapportActiviteAvisNotificationRule(RapportActiviteAvisNotificationRule $rapportActiviteAvisNotificationRule): void
    {
        $this->rapportActiviteAvisNotificationRule = $rapportActiviteAvisNotificationRule;
    }
}