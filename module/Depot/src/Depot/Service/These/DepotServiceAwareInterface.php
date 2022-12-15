<?php

namespace Depot\Service\These;

interface DepotServiceAwareInterface
{
    public function setDepotService(DepotService $depotService);
}