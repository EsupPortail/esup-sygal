<?php

namespace Application\Assertion\Interfaces;

use Application\Service\UserContextServiceAwareInterface;

interface EntityAssertionInterface extends UserContextServiceAwareInterface
{
    /**
     * @param string $privilege
     *
     * @return boolean
     */
    public function assert($privilege = null);
}