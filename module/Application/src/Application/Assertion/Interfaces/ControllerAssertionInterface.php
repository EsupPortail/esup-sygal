<?php

namespace Application\Assertion\Interfaces;

use Application\Service\UserContextServiceAwareInterface;

interface ControllerAssertionInterface extends UserContextServiceAwareInterface
{
    /**
     * @param string $controller
     * @param null   $action
     * @param null   $privilege
     * @return bool
     */
    public function assert($controller, $action = null, $privilege = null);
}