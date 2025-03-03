<?php

namespace Application\Service\VersionDiplome;

trait VersionDiplomeServiceAwareTrait
{
    protected VersionDiplomeService $versionDiplomeService;

    public function setVersionDiplomeService(VersionDiplomeService $versionDiplomeService): void
    {
        $this->versionDiplomeService = $versionDiplomeService;
    }


}