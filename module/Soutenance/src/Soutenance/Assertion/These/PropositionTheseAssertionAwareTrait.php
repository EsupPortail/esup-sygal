<?php

namespace Soutenance\Assertion\These;

trait PropositionTheseAssertionAwareTrait {

    /** @var PropositionTheseAssertion */
    private $propositionTheseAssertion;

    /**
     * @return PropositionTheseAssertion
     */
    public function getPropositionTheseAssertion(): PropositionTheseAssertion
    {
        return $this->propositionTheseAssertion;
    }

    /**
     * @param PropositionTheseAssertion $propositionTheseAssertion
     */
    public function setPropositionTheseAssertion(PropositionTheseAssertion $propositionTheseAssertion): void
    {
        $this->propositionTheseAssertion = $propositionTheseAssertion;
    }


}