<?php

namespace Application\Service\Source;

/**
 * @author Unicaen
 */
trait SourceServiceAwareTrait
{
    protected SourceService $sourceService;

    public function setSourceService(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }
}