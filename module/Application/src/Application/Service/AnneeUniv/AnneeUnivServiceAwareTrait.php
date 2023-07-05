<?php

namespace Application\Service\AnneeUniv;

trait AnneeUnivServiceAwareTrait
{
    protected AnneeUnivService $anneeUnivService;

    public function setAnneeUnivService(AnneeUnivService $anneeUnivService): void
    {
        $this->anneeUnivService = $anneeUnivService;
    }
}