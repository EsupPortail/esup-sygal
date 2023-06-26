<?php

namespace These\Service\CoEncadrant;

trait CoEncadrantServiceAwareTrait
{

    private CoEncadrantService $coEncadrantService;

    public function getCoEncadrantService(): CoEncadrantService
    {
        return $this->coEncadrantService;
    }

    public function setCoEncadrantService(CoEncadrantService $coEncadrantService): void
    {
        $this->coEncadrantService = $coEncadrantService;
    }
}