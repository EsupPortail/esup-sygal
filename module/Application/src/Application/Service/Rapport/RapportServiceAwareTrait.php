<?php

namespace Application\Service\Rapport;

trait RapportServiceAwareTrait
{
    /**
     * @var RapportService
     */
    protected $rapportService;

    /**
     * @param RapportService $rapportService
     */
    public function setRapportService(RapportService $rapportService)
    {
        $this->rapportService = $rapportService;
    }
}