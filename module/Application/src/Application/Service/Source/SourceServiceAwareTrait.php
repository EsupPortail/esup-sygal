<?php

namespace Application\Service\Source;

/**
 * @author Unicaen
 */
trait SourceServiceAwareTrait
{
    /**
     * @var SourceService
     */
    protected $sourceService;

    /**
     * @param SourceService $sourceService
     * @return self
     */
    public function setSourceService(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;

        return $this;
    }
}