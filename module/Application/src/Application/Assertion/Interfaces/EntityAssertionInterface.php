<?php

namespace Application\Assertion\Interfaces;

use Application\Service\UserContextServiceAwareInterface;

interface EntityAssertionInterface extends UserContextServiceAwareInterface
{
    /**
     * @param array $context
     */
    public function setContext(array $context);

    /**
     * @param string $privilege
     *
     * @return boolean
     */
    public function assert($privilege = null);
}