<?php

namespace InscriptionAdministrative\Rule\Reception;

trait ReceptionRuleAwareTrait
{
    protected ReceptionRuleInterface $receptionRule;

    public function setReceptionRule(ReceptionRuleInterface $receptionRule): void
    {
        $this->receptionRule = $receptionRule;
    }
}