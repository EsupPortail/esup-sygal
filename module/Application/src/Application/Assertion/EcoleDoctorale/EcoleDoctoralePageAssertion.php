<?php

namespace Application\Assertion\EcoleDoctorale;

use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\AuthorizeServiceAwareTrait;

class EcoleDoctoralePageAssertion implements PageAssertionInterface
{
    use AuthorizeServiceAwareTrait;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
    }

    /**
     * @param array $page
     * @return bool
     */
    public function assert(array $page)
    {
        return true;
    }
}