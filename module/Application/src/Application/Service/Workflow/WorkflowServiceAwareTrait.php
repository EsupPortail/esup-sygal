<?php

namespace Application\Service\Workflow;

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