<?php

namespace RapportActivite\Service;

trait RapportActiviteServiceAwareTrait
{
    protected RapportActiviteService $rapportActiviteService;

    public function setRapportActiviteService(RapportActiviteService $rapportActiviteService): void
    {
        $this->rapportActiviteService = $rapportActiviteService;
    }
}