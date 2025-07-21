<?php

namespace Depot\Dispatch\Depot;

trait DispatchServiceAwareTrait
{
    protected DispatchService $depotDispatchService;

    public function setDepotDispatchService(DispatchService $depotDispatchService): void
    {
        $this->depotDispatchService = $depotDispatchService;
    }
}