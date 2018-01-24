<?php

namespace Application\Service\Notification;

interface NotificationServiceAwareInterface
{
    public function setNotificationService(NotificationService $notificationService);
}