<?php

namespace Application\Service\Variable;

interface VariableServiceAwareInterface
{
    public function setVariableService(VariableService $envService);
}