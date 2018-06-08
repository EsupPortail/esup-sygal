<?php

namespace Application\Service\Notification;

interface NotifierServiceAwareInterface
{
    public function setNotifierService(NotifierService $notifierService);
}