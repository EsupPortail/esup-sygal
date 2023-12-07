<?php

namespace Substitution\Service\Doublon;

use Substitution\Service\Doublon\DoublonService;

trait DoublonServiceAwareTrait
{
    protected DoublonService $doublonService;

    public function setDoublonService(DoublonService $doublonService): void
    {
        $this->doublonService = $doublonService;
    }
}