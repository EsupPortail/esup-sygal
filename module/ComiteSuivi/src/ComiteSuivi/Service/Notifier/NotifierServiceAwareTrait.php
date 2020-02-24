<?php

namespace ComiteSuivi\Service\Notifier;

trait NotifierServiceAwareTrait {

    /** @var NotifierService */
    private $notifierService;

    /**
     * @return NotifierService
     */
    public function getNotifierService()
    {
        return $this->notifierService;
    }

    /**
     * @param NotifierService $notifierService
     * @return NotifierService
     */
    public function setNotifierService($notifierService)
    {
        $this->notifierService = $notifierService;
        return $this->notifierService;
    }


}