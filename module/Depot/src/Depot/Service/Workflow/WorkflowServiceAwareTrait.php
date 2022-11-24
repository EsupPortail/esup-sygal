<?php

namespace Depot\Service\Workflow;

use Depot\Service\Workflow\WorkflowService;

trait WorkflowServiceAwareTrait
{
    /**
     * @var WorkflowService
     */
    protected $workflowService;

    /**
     * @param WorkflowService $workflowService
     */
    public function setWorkflowService(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }
}