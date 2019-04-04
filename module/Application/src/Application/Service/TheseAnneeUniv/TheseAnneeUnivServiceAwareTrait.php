<?php

namespace Application\Service\TheseAnneeUniv;

trait TheseAnneeUnivServiceAwareTrait
{
    /**
     * @var TheseAnneeUnivService
     */
    protected $theseAnneeUnivService;

    /**
     * @param TheseAnneeUnivService $service
     */
    public function setTheseAnneeUnivService(TheseAnneeUnivService $service)
    {
        $this->theseAnneeUnivService = $service;
    }
}