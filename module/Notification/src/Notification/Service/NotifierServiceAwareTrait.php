<?php

namespace Notification\Service;

trait NotifierServiceAwareTrait
{
    /**
     * @var NotifierService
     */
    protected $notificationService;

    /**
     * @param NotifierService $notificationService
     */
    public function setNotificationService(NotifierService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
}