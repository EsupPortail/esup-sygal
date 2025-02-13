<?php

namespace Acteur\Service\ActeurHDR;

trait ActeurHDRServiceAwareTrait
{
    protected ActeurHDRService $acteurHDRService;

    public function setActeurHDRService(ActeurHDRService $acteurService): void
    {
        $this->acteurHDRService = $acteurService;
    }

    public function getActeurHDRService(): ActeurHDRService
    {
        return $this->acteurHDRService;
    }
}