<?php

namespace Substitution\Service;

trait LogServiceAwareTrait
{
    protected LogService $logService;

    public function setLogService(LogService $logService): void
    {
        $this->logService = $logService;
    }
}