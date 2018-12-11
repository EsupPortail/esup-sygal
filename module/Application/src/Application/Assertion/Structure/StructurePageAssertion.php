<?php

namespace Application\Assertion\Structure;

use Application\Acl\WfEtapeResource;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\AuthorizeServiceAwareTrait;
use Application\Service\UserContextService;

/**
 * Class StructureAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class StructurePageAssertion implements PageAssertionInterface
{
    use AuthorizeServiceAwareTrait;

    protected $structure;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->structure = isset($context['structure']) ? $context['structure'] : null;
    }

    /**
     * @param array $page
     * @return bool
     */
    public function assert(array $page)
    {
        $etape = isset($page['etape']) ? $page['etape'] : null;
        if (!$etape) {
            return true;
        }

        if ($this->structure && ! $this->authorizeService->isAllowed(new WfEtapeResource($etape, $this->structure))) {
            return false;
        }

        return true;
    }
}