<?php

namespace Individu\Service;

trait IndividuServiceAwareTrait
{
    protected IndividuService $individuService;

    public function getIndividuService(): IndividuService
    {
        return $this->individuService;
    }

    public function setIndividuService(IndividuService $individuService): void
    {
        $this->individuService = $individuService;
    }
}