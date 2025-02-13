<?php

namespace Acteur\Hydrator\ActeurHDR;

trait ActeurHDRHydratorAwareTrait
{
    protected ActeurHDRHydrator $acteurHDRHydrator;

    public function setActeurHDRHydrator(ActeurHDRHydrator $acteurHDRHydrator): void
    {
        $this->acteurHDRHydrator = $acteurHDRHydrator;
    }
}