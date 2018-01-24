<?php

namespace Application\Service\These;

trait TheseServiceAwareTrait
{
    /**
     * @var TheseService
     */
    protected $theseService;

    /**
     * @param TheseService $theseService
     */
    public function setTheseService(TheseService $theseService)
    {
        $this->theseService = $theseService;
    }
}