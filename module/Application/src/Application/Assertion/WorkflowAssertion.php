<?php

namespace Application\Assertion;

use Application\Acl\WfEtapeResource;
use Application\Service\UserContextService;
use Application\Service\Workflow\WorkflowServiceAwareInterface;
use Application\Service\Workflow\WorkflowServiceAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class WorkflowAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class WorkflowAssertion extends AbstractAssertion implements WorkflowServiceAwareInterface
{
    use WorkflowServiceAwareTrait;

    /**
     * @param ResourceInterface $resource
     * @param string          $privilege
     *
     * @return boolean
     */
    protected function assertOther(ResourceInterface $resource = null, $privilege = null)
    {
        /** @var WfEtapeResource $resource */
        $r = $this->workflowService->findOneByEtape($resource->getThese(), $resource->getEtape());

        if ($r->estNull()) {
            return false;
        }

        return $r->getAtteignable();
    }
}