<?php

namespace Soutenance\Assertion;

use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Provider\Privilege\SoutenancePrivileges;

class EngagementImpartialiteEntityAssertion implements EntityAssertionInterface {
    use UserContextServiceAwareTrait;

    public function assert($privilege = null)
    {
        switch ($privilege) {
            case SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER :
                return true;
        }
        return false;
    }
    
}



