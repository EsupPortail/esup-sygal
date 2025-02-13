<?php

namespace Candidat\Service;

trait CandidatServiceAwareTrait
{
    /**
     * @var CandidatService
     */
    protected $candidatService;

    /**
     * @param CandidatService $candidatService
     */
    public function setCandidatService(CandidatService $candidatService)
    {
        $this->candidatService = $candidatService;
    }
}