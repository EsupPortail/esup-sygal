<?php

namespace StepStar\Service\Oai;

trait OaiServiceAwareTrait
{
    protected OaiService $oaiSetService;

    /**
     * @param \StepStar\Service\Oai\OaiService $oaiSetService
     */
    public function setOaiSetService(OaiService $oaiSetService): void
    {
        $this->oaiSetService = $oaiSetService;
    }
}