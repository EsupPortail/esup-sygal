<?php

namespace Notification\Entity\Service;

trait NotifEntityServiceAwareTrait
{
    /**
     * @var NotifEntityService
     */
    protected $notifEntityService;

    /**
     * @param NotifEntityService $notifEntityService
     */
    public function setNotifEntityService(NotifEntityService $notifEntityService)
    {
        $this->notifEntityService = $notifEntityService;
    }
}