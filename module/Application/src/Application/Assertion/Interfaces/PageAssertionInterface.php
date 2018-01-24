<?php

namespace Application\Assertion\Interfaces;

use Application\Service\AuthorizeServiceAwareInterface;

interface PageAssertionInterface extends AuthorizeServiceAwareInterface
{
    /**
     * @param array $page
     * @return bool
     */
    public function assert(array $page);
}