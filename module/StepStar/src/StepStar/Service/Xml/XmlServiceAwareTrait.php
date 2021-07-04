<?php

namespace StepStar\Service\Xml;

trait XmlServiceAwareTrait
{
    /**
     * @var XmlService
     */
    protected $xmlService;

    /**
     * @param XmlService $xmlService
     * @return self
     */
    public function setXmlService(XmlService $xmlService): self
    {
        $this->xmlService = $xmlService;
        return $this;
    }


}