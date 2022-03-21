<?php

namespace StepStar\Service\Fetch;

trait FetchServiceAwareTrait
{
    protected FetchService $fetchService;

    /**
     * @param \StepStar\Service\Fetch\FetchService $fetchService
     */
    public function setFetchService(FetchService $fetchService): void
    {
        $this->fetchService = $fetchService;
    }
}