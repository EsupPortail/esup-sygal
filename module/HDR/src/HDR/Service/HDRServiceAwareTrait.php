<?php

namespace HDR\Service;

trait HDRServiceAwareTrait {

    /** @var HDRService */
    private $hdrService;

    /**
     * @return HDRService
     */
    public function getHDRService()
    {
        return $this->hdrService;
    }

    /**
     * @param HDRService $hdr
     * @return HDRService
     */
    public function setHDRService($hdr)
    {
        $this->hdrService = $hdr;
        return $this->hdrService;
    }
}