<?php

namespace StepStar\Facade\Generate;

trait GenerateFacadeAwareTrait
{
    protected GenerateFacade $generateFacade;

    public function setGenerateFacade(GenerateFacade $generateFacade): void
    {
        $this->generateFacade = $generateFacade;
    }
}