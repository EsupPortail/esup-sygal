<?php

namespace Substitution\Service;

trait DoublonServiceAwareTrait
{
    protected DoublonService $doublonService;

    public function setDoublonService(DoublonService $doublonService): void
    {
        $this->doublonService = $doublonService;
    }
}