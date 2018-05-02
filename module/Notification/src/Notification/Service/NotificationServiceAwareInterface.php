<?php

namespace Notification\Service;

interface NotificationServiceAwareInterface
{
    public function setNotificationService(NotificationService $notificationService);
}