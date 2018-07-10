<?php

namespace Import\Service\Traits;

use Import\Service\FetcherService;

trait FetcherServiceAwareTrait
{
    /**
     * @var FetcherService
     */
    protected $fetcherService;

    /**
     * @param FetcherService $fetcherService
     * @return self
     */
    public function setFetcherService(FetcherService $fetcherService)
    {
        $this->fetcherService = $fetcherService;

        return $this;
    }
}