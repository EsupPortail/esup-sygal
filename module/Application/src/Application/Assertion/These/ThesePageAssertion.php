<?php

namespace Application\Assertion\These;

use Application\Acl\WfEtapeResource;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\AuthorizeServiceAwareTrait;
use Application\Service\UserContextService;

/**
 * Class TheseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
class ThesePageAssertion implements PageAssertionInterface
{
    use AuthorizeServiceAwareTrait;

    protected $these;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->these = isset($context['these']) ? $context['these'] : null;
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

        if ($this->these && ! $this->authorizeService->isAllowed(new WfEtapeResource($etape, $this->these))) {
            return false;
        }

        return true;
    }
}