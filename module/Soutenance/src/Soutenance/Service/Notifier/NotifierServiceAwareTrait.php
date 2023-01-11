<?php

namespace Soutenance\Service\Notifier;

trait NotifierServiceAwareTrait {

    private NotifierService $soutenanceNotifierService;

    public function getSoutenanceNotifierService(): NotifierService
    {
        return $this->soutenanceNotifierService;
    }

    public function setSoutenanceNotifierService(NotifierService $soutenanceNotifierService): void
    {
        $this->soutenanceNotifierService = $soutenanceNotifierService;
    }


}