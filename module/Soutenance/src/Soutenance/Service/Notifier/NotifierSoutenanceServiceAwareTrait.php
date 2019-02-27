<?php

namespace Soutenance\Service\Notifier;

trait NotifierSoutenanceServiceAwareTrait {

    /** @var NotifierSoutenanceService $notifierSoutenanceService */
    private $notifierSoutenanceService;

    /**
     * @return NotifierSoutenanceService
     */
    public function getNotifierSoutenanceService()
    {
        return $this->notifierSoutenanceService;
    }

    /**
     * @param NotifierSoutenanceService $notifierSoutenanceService
     * @return NotifierSoutenanceService
     */
    public function setNotifierSoutenanceService($notifierSoutenanceService)
    {
        $this->notifierSoutenanceService = $notifierSoutenanceService;
        return $this->getNotifierSoutenanceService();
    }


}