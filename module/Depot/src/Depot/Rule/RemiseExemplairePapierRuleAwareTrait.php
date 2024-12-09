<?php

namespace Depot\Rule;

trait RemiseExemplairePapierRuleAwareTrait
{
    protected RemiseExemplairePapierRule $remiseExemplairePapierRule;

    public function setRemiseExemplairePapierRule(RemiseExemplairePapierRule $remiseExemplairePapierRule): void
    {
        $this->remiseExemplairePapierRule = $remiseExemplairePapierRule;
    }
}