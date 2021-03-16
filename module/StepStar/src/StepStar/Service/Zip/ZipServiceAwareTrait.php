<?php

namespace StepStar\Service\Zip;

trait ZipServiceAwareTrait
{
    /**
     * @var ZipService
     */
    protected $zipService;

    /**
     * @param ZipService $zipService
     * @return self
     */
    public function setZipService(ZipService $zipService): self
    {
        $this->zipService = $zipService;
        return $this;
    }


}