<?php

namespace Information\Service\InformationLangue;

trait InformationLangueServiceAwareTrait
{
    protected InformationLangueService $informationLangueService;

    public function setInformationLangueService(InformationLangueService $informationLangueService): void
    {
        $this->informationLangueService = $informationLangueService;
    }
}