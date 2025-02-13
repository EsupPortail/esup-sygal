<?php

namespace  Soutenance\Service\Proposition\PropositionHDR;

trait PropositionHDRServiceAwareTrait {

    /** @var PropositionHDRService */
    private $propositionHDRService;

    /**
     * @return PropositionHDRService
     */
    public function getPropositionHDRService()
    {
        return $this->propositionHDRService;
    }

    /**
     * @param PropositionHDRService $propositionHDRService
     * @return PropositionHDRService
     */
    public function setPropositionHDRService($propositionHDRService)
    {
        $this->propositionHDRService = $propositionHDRService;
        return $this->propositionHDRService;
    }
}