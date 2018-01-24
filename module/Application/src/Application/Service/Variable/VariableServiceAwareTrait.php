<?php

namespace Application\Service\Variable;

trait VariableServiceAwareTrait
{
    /**
     * @var VariableService
     */
    protected $variableService;

    /**
     * @param VariableService $variableService
     */
    public function setVariableService(VariableService $variableService)
    {
        $this->variableService = $variableService;
    }
}