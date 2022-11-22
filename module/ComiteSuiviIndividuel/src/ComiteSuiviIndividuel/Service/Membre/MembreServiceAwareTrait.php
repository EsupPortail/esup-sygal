<?php

namespace ComiteSuiviIndividuel\Service\Membre;

trait MembreServiceAwareTrait {

    private MembreService $membreService;

    public function getMembreService(): MembreService
    {
        return $this->membreService;
    }

    public function setMembreService(MembreService $membreService): void
    {
        $this->membreService = $membreService;
    }

}