<?php

namespace Depot\Service\Workflow;

interface WorkflowServiceAwareInterface
{
    public function setWorkflowService(WorkflowService $fichierService);
}