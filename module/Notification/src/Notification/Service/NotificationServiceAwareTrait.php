<?php

namespace Notification\Service;

trait NotificationServiceAwareTrait
{
    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @param NotificationService $notificationService
     */
    public function setNotificationService(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
}