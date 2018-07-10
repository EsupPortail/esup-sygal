<?php

namespace Import\Service\Traits;

use Import\Service\SynchroService;

trait SynchroServiceAwareTrait
{
    /**
     * @var SynchroService
     */
    protected $synchroService;

    /**
     * @param SynchroService $synchroService
     * @return self
     */
    public function setSynchroService(SynchroService $synchroService)
    {
        $this->synchroService = $synchroService;

        return $this;
    }
}