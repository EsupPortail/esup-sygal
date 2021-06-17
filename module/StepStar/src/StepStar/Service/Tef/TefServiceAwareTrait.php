<?php

namespace StepStar\Service\Tef;

trait TefServiceAwareTrait
{
    /**
     * @var TefService
     */
    protected $tefService;

    /**
     * @param TefService $xmlService
     * @return self
     */
    public function setTefService(TefService $xmlService): self
    {
        $this->tefService = $xmlService;
        return $this;
    }


}