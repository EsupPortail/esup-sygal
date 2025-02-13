<?php

namespace HDR\Service;

trait HDRSearchServiceAwareTrait
{
    /**
     * @var HDRSearchService
     */
    protected $hdrSearchService;

    /**
     * @param HDRSearchService $hdrSearchService
     */
    public function setHDRSearchService(HDRSearchService $hdrSearchService)
    {
        $this->hdrSearchService = $hdrSearchService;
    }
}