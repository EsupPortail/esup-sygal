<?php

namespace  Soutenance\Service\Proposition\PropositionThese;

trait PropositionTheseServiceAwareTrait {

    /** @var PropositionTheseService */
    private $propositionTheseService;

    /**
     * @return PropositionTheseService
     */
    public function getPropositionTheseService()
    {
        return $this->propositionTheseService;
    }

    /**
     * @param PropositionTheseService $propositionTheseService
     * @return PropositionTheseService
     */
    public function setPropositionTheseService($propositionTheseService)
    {
        $this->propositionTheseService = $propositionTheseService;
        return $this->propositionTheseService;
    }
}