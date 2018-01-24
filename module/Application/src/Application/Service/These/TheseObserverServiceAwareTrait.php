<?php

namespace Application\Service\These;

trait TheseObserverServiceAwareTrait
{
    /**
     * @var TheseObserverService
     */
    protected $theseObserverService;

    /**
     * @param TheseObserverService $theseObserverService
     * @return void
     */
    public function setTheseObserverService(TheseObserverService $theseObserverService)
    {
        $this->theseObserverService = $theseObserverService;
    }
}