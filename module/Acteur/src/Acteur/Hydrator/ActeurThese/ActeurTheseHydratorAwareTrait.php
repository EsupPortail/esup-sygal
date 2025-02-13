<?php

namespace Acteur\Hydrator\ActeurThese;

trait ActeurTheseHydratorAwareTrait
{
    protected ActeurTheseHydrator $acteurTheseHydrator;

    public function setActeurTheseHydrator(ActeurTheseHydrator $acteurTheseHydrator): void
    {
        $this->acteurTheseHydrator = $acteurTheseHydrator;
    }
}