<?php

namespace Application\Assertion\Interfaces;

use Application\Service\AuthorizeServiceAwareInterface;

interface PageAssertionInterface extends AuthorizeServiceAwareInterface
{
    /**
     * @param array $context
     */
    public function setContext(array $context);

    /**
     * @param array $page
     * @return bool
     */
    public function assert(array $page);
}