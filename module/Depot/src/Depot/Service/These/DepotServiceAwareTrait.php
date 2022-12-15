<?php

namespace Depot\Service\These;

trait DepotServiceAwareTrait
{
    /**
     * @var DepotService
     */
    protected DepotService $depotService;

    /**
     * @param DepotService $depotService
     */
    public function setDepotService(DepotService $depotService)
    {
        $this->depotService = $depotService;
    }

    /**
     * @deprecated
     */
    public function getDepotService(): DepotService
    {
        return $this->depotService;
    }
}