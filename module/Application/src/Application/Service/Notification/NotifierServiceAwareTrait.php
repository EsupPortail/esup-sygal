<?php

namespace Application\Service\Notification;

trait NotifierServiceAwareTrait
{
    /**
     * @var NotifierService
     */
    protected $notifierService;

    /**
     * @param NotifierService $notifierService
     */
    public function setNotifierService(NotifierService $notifierService)
    {
        $this->notifierService = $notifierService;
    }
}