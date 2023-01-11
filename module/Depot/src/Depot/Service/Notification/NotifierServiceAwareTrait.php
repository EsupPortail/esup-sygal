<?php

namespace Depot\Service\Notification;

trait NotifierServiceAwareTrait
{
    protected NotifierService $depotNotifierService;

    public function setDepotNotifierService(NotifierService $depotNotifierService)
    {
        $this->depotNotifierService = $depotNotifierService;
    }
}