<?php

namespace These\Service\TheseAnneeUniv;

trait TheseAnneeUnivServiceAwareTrait
{
    protected TheseAnneeUnivService $theseAnneeUnivService;

    /**
     * @deprecated Utiliser {@see setTheseAnneeUnivService()} à la place
     */
    public function setAnneesUnivs(TheseAnneeUnivService $service): void
    {
        $this->theseAnneeUnivService = $service;
    }

    public function setTheseAnneeUnivService(TheseAnneeUnivService $theseAnneeUnivService): void
    {
        $this->theseAnneeUnivService = $theseAnneeUnivService;
    }
}