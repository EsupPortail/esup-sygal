<?php

namespace  Soutenance\Service\Proposition;

trait PropositionServiceAwareTrait {

    /** @var PropositionService */
    private $propositionService;

    /**
     * @return PropositionService
     */
    public function getPropositionService()
    {
        return $this->propositionService;
    }

    /**
     * @param PropositionService $propositionService
     * @return PropositionService
     */
    public function setPropositionService($propositionService)
    {
        $this->propositionService = $propositionService;
        return $this->propositionService;
    }

}