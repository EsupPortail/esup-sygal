<?php

namespace Application\Service\Env;

interface EnvServiceAwareInterface
{
    public function setEnvService(EnvService $envService);
}