<?php

namespace Soutenance\Rule;

trait PropositionJuryRuleAwareTrait
{
    protected PropositionJuryRule $propositionJuryRule;

    public function setPropositionJuryRule(PropositionJuryRule $propositionJuryRule): void
    {
        $this->propositionJuryRule = $propositionJuryRule;
    }
}