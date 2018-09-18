<?php

namespace Application\Assertion\EcoleDoctorale;

use Application\Assertion\ControllerAssertion;
use Application\Service\UserContextServiceAwareTrait;

class EcoleDoctoraleControllerAssertion extends ControllerAssertion
{
    use UserContextServiceAwareTrait;

    /**
     * @param string $privilege
     * @return bool
     */
    public function assert($privilege = null)
    {
        return true;
    }
}