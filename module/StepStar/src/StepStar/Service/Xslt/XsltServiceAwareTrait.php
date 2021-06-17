<?php

namespace StepStar\Service\Xslt;

trait XsltServiceAwareTrait
{
    /**
     * @var XsltService
     */
    protected $xsltService;

    /**
     * @param XsltService $xsltService
     * @return self
     */
    public function setXsltService(XsltService $xsltService): self
    {
        $this->xsltService = $xsltService;
        return $this;
    }


}