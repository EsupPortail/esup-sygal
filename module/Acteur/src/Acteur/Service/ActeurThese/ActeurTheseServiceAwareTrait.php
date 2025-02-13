<?php

namespace Acteur\Service\ActeurThese;

trait ActeurTheseServiceAwareTrait
{
    protected ActeurTheseService $acteurTheseService;

    public function setActeurTheseService(ActeurTheseService $acteurService): void
    {
        $this->acteurTheseService = $acteurService;
    }

    public function getActeurTheseService(): ActeurTheseService
    {
        return $this->acteurTheseService;
    }
}