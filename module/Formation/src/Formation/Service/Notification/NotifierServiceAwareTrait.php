<?php

namespace Formation\Service\Notification;

trait NotifierServiceAwareTrait
{
    private NotifierService $notifierService;

    public function getNotifierService(): NotifierService
    {
        return $this->notifierService;
    }

    public function setNotifierService(NotifierService $notifierService): void
    {
        $this->notifierService = $notifierService;
    }
}