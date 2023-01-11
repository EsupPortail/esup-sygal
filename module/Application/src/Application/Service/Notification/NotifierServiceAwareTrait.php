<?php

namespace Application\Service\Notification;

trait NotifierServiceAwareTrait
{
    protected NotifierService $applicationNotifierService;

    public function setApplicationNotifierService(NotifierService $applicationNotifierService)
    {
        $this->applicationNotifierService = $applicationNotifierService;
    }
}