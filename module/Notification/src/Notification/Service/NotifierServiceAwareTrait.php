<?php

namespace Notification\Service;

trait NotifierServiceAwareTrait
{
    protected NotifierService $notifierService;

    public function setNotifierService(NotifierService $notifierService)
    {
        $this->notifierService = $notifierService;
    }
}