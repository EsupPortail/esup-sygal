<?php

namespace Soutenance\Assertion\HDR;

trait PropositionHDRAssertionAwareTrait {

    /** @var PropositionHDRAssertion */
    private $propositionHDRAssertion;

    /**
     * @return PropositionHDRAssertion
     */
    public function getPropositionHDRAssertion(): PropositionHDRAssertion
    {
        return $this->propositionHDRAssertion;
    }

    /**
     * @param PropositionHDRAssertion $propositionHDRAssertion
     */
    public function setPropositionHDRAssertion(PropositionHDRAssertion $propositionHDRAssertion): void
    {
        $this->propositionHDRAssertion = $propositionHDRAssertion;
    }
}