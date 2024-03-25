<?php

namespace StepStar;

trait CleanableAfterWorkTrait
{
    protected bool $cleanAfterWork = false;

    public function setCleanAfterWork(bool $cleanAfterWork = true): void
    {
        $this->cleanAfterWork = $cleanAfterWork;
    }
}