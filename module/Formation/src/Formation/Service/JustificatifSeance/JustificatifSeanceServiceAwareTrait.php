<?php

namespace Formation\Service\JustificatifSeance;

trait JustificatifSeanceServiceAwareTrait {

    private JustificatifSeanceService $justificatifSeanceService;

    /**
     * @return JustificatifSeanceService
     */
    public function getJustificatifSeanceService(): JustificatifSeanceService
    {
        return $this->justificatifSeanceService;
    }

    /**
     * @param JustificatifSeanceService $justificatifSeanceService
     */
    public function setJustificatifSeanceService(JustificatifSeanceService $justificatifSeanceService): void
    {
        $this->justificatifSeanceService = $justificatifSeanceService;
    }
}