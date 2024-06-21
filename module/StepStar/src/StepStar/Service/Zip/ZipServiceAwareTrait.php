<?php

namespace StepStar\Service\Zip;

trait ZipServiceAwareTrait
{
    protected ZipService $zipService;

    public function setZipService(ZipService $zipService): void
    {
        $this->zipService = $zipService;
    }
}