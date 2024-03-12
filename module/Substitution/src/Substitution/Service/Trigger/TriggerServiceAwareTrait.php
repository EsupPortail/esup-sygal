<?php

namespace Substitution\Service\Trigger;

trait TriggerServiceAwareTrait
{
    protected TriggerService $triggerService;

    public function setTriggerService(TriggerService $triggerService): void
    {
        $this->triggerService = $triggerService;
    }
}