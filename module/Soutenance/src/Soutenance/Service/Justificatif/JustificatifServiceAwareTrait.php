<?php

namespace Soutenance\Service\Justificatif;

trait JustificatifServiceAwareTrait {

    /** @var JustificatifService */
    private $justificatifService;

    /**
     * @return JustificatifService
     */
    public function getJustificatifService()
    {
        return $this->justificatifService;
    }

    /**
     * @param JustificatifService $justificatifService
     * @return JustificatifService
     */
    public function setJustificatifService($justificatifService)
    {
        $this->justificatifService = $justificatifService;
        return $this->justificatifService;
    }
}