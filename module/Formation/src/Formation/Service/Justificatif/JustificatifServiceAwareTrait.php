<?php

namespace Formation\Service\Justificatif;

trait JustificatifServiceAwareTrait {

    private JustificatifService $justificatifService;

    /**
     * @return JustificatifService
     */
    public function getJustificatifService(): JustificatifService
    {
        return $this->justificatifService;
    }

    /**
     * @param JustificatifService $justificatifService
     */
    public function setJustificatifService(JustificatifService $justificatifService): void
    {
        $this->justificatifService = $justificatifService;
    }

}