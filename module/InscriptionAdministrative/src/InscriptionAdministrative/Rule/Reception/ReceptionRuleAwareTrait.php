<?php

namespace InscriptionAdministrative\Rule\Reception;

trait ReceptionRuleAwareTrait
{
    protected ReceptionRule $receptionRule;

    public function setReceptionRule(ReceptionRule $receptionRule): void
    {
        $this->receptionRule = $receptionRule;
    }
}