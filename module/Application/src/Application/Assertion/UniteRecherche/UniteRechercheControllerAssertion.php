<?php

namespace Application\Assertion\UniteRecherche;

use Application\Assertion\ControllerAssertion;
use Application\Service\UserContextServiceAwareTrait;

class UniteRechercheControllerAssertion extends ControllerAssertion
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