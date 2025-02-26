<?php

namespace InscriptionAdministrative\Rule\Reception;

trait ReceptionRulePluginManagerAwareTrait
{
    protected ReceptionRulePluginManager $receptionRulePluginManager;

    public function setReceptionRulePluginManager(ReceptionRulePluginManager $receptionRulePluginManager): void
    {
        $this->receptionRulePluginManager = $receptionRulePluginManager;
    }
}