<?php

namespace Application\Service\Workflow;

interface WorkflowServiceAwareInterface
{
    public function setWorkflowService(WorkflowService $fichierService);
}