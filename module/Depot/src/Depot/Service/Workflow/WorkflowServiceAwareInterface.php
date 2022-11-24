<?php

namespace Depot\Service\Workflow;

use Depot\Service\Workflow\WorkflowService;

interface WorkflowServiceAwareInterface
{
    public function setWorkflowService(WorkflowService $fichierService);
}