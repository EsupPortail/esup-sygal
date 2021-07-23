<?php

namespace Formation\Service\Notification;

trait NotificationServiceAwareTrait {

    /** @var NotificationService */
    private $notificationService;

    /**
     * @return NotificationService
     */
    public function getNotificationService(): NotificationService
    {
        return $this->notificationService;
    }

    /**
     * @param NotificationService $notificationService
     * @return NotificationService
     */
    public function setNotificationService(NotificationService $notificationService): NotificationService
    {
        $this->notificationService = $notificationService;
        return $this->notificationService;
    }


}