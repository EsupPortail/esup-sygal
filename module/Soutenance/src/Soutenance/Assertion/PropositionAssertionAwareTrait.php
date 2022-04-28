<?php

namespace Soutenance\Assertion;

trait PropositionAssertionAwareTrait {

    /** @var PropositionAssertion */
    private $propositionAssertion;

    /**
     * @return PropositionAssertion
     */
    public function getPropositionAssertion(): PropositionAssertion
    {
        return $this->propositionAssertion;
    }

    /**
     * @param PropositionAssertion $propositionAssertion
     */
    public function setPropositionAssertion(PropositionAssertion $propositionAssertion): void
    {
        $this->propositionAssertion = $propositionAssertion;
    }


}