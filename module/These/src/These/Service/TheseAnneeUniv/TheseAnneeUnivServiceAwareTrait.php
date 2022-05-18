<?php

namespace These\Service\TheseAnneeUniv;

trait TheseAnneeUnivServiceAwareTrait
{
    /**
     * @var TheseAnneeUnivService
     */
    protected $theseAnneeUnivService;

    /**
     * @param TheseAnneeUnivService $service
     */
    public function setAnneesUnivs(TheseAnneeUnivService $service)
    {
        $this->theseAnneeUnivService = $service;
    }
}