<?php

namespace Application\Service\These;

trait TheseRechercheServiceAwareTrait
{
    /**
     * @var TheseRechercheService
     */
    protected $theseRechercheService;

    /**
     * @param TheseRechercheService $theseRechercheService
     */
    public function setTheseRechercheService(TheseRechercheService $theseRechercheService)
    {
        $this->theseRechercheService = $theseRechercheService;
    }
}