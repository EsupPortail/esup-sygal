<?php

namespace Application\Service\Env;

trait EnvServiceAwareTrait
{
    /**
     * @var EnvService
     */
    protected $envService;

    /**
     * @param EnvService $envService
     */
    public function setEnvService(EnvService $envService)
    {
        $this->envService = $envService;
    }
}