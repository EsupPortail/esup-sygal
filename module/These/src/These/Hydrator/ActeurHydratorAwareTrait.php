<?php

namespace These\Hydrator;

trait ActeurHydratorAwareTrait
{
    protected ActeurHydrator $acteurHydrator;

    public function setActeurHydrator(ActeurHydrator $acteurHydrator): void
    {
        $this->acteurHydrator = $acteurHydrator;
    }
}