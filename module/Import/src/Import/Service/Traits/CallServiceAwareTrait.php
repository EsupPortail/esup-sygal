<?php

namespace Import\Service\Traits;

use Import\Service\CallService;

trait CallServiceAwareTrait
{
    /**
     * @var CallService
     */
    protected $callService;

    /**
     * @param CallService $callService
     * @return self
     */
    public function setCallService(CallService $callService)
    {
        $this->callService = $callService;

        return $this;
    }
}