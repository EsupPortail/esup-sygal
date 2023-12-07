<?php

namespace Substitution\Service\Log;

use Substitution\Service\Log\LogService;

trait LogServiceAwareTrait
{
    protected LogService $logService;

    public function setLogService(LogService $logService): void
    {
        $this->logService = $logService;
    }
}