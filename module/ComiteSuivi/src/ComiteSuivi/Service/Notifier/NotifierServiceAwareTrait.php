<?php

namespace ComiteSuivi\Service\Notifier;

trait NotifierServiceAwareTrait {

    /** @var NotifierService */
    private $notifierService;

    /**
     * @return NotifierService
     */
    public function getNotifierService() : NotifierService
    {
        return $this->notifierService;
    }

    /**
     * @param NotifierService $notifierService
     * @return NotifierService
     */
    public function setNotifierService(NotifierService $notifierService) : NotifierService
    {
        $this->notifierService = $notifierService;
        return $this->notifierService;
    }


}