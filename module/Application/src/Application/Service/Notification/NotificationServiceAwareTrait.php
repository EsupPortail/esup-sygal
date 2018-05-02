<?php

namespace Application\Service\Notification;

trait NotificationServiceAwareTrait
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