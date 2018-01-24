<?php

namespace Application\Service\Notification;

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