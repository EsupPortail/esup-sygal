<?php

namespace Application\Service\RapportAnnuel;

trait RapportAnnuelServiceAwareTrait
{
    /**
     * @var RapportAnnuelService
     */
    protected $rapportAnnuelService;

    /**
     * @param RapportAnnuelService $rapportAnnuelService
     */
    public function setRapportAnnuelService(RapportAnnuelService $rapportAnnuelService)
    {
        $this->rapportAnnuelService = $rapportAnnuelService;
    }
}