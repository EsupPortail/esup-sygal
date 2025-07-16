<?php

namespace Application\Rule\PluginManager;

trait RulePluginManagerAwareTrait
{
    protected RulePluginManager $rulePluginManager;

    public function setRulePluginManager(RulePluginManager $rulePluginManager): void
    {
        $this->rulePluginManager = $rulePluginManager;
    }
}