<?php

namespace Application\Assertion\Interfaces;

use Application\Service\UserContextServiceAwareInterface;

interface ControllerAssertionInterface extends UserContextServiceAwareInterface
{
    /**
     * @param array $context
     */
    public function setContext(array $context);

    /**
     * @param string $privilege
     * @return bool
     */
    public function assert($privilege = null);
}