<?php

namespace StepStar\Service\Xsl;

trait XslServiceAwareTrait
{
    /**
     * @var XslService
     */
    protected XslService $xslService;

    /**
     * @param XslService $xslService
     * @return self
     */
    public function setXslService(XslService $xslService): self
    {
        $this->xslService = $xslService;
        return $this;
    }


}