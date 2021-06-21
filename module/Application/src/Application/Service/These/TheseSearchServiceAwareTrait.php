<?php

namespace Application\Service\These;

trait TheseSearchServiceAwareTrait
{
    /**
     * @var TheseSearchService
     */
    protected $theseSearchService;

    /**
     * @param TheseSearchService $theseSearchService
     */
    public function setTheseSearchService(TheseSearchService $theseSearchService)
    {
        $this->theseSearchService = $theseSearchService;
    }
}