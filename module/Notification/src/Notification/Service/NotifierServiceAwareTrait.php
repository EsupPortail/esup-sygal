<?php

namespace Notification\Service;

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

    /**
     * @return NotifierService
     */
    public function getNotifierService(): NotifierService
    {
        return $this->notifierService;
    }


}