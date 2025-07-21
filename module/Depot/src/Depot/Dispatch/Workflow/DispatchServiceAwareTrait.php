<?php

namespace Depot\Dispatch\Workflow;

trait DispatchServiceAwareTrait
{
    protected DispatchService $workflowDispatchService;

    public function setWorkflowDispatchService(DispatchService $workflowDispatchService): void
    {
        $this->workflowDispatchService = $workflowDispatchService;
    }
}