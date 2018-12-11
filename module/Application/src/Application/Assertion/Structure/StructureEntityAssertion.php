<?php

namespace Application\Assertion\Structure;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;

class StructureEntityAssertion implements UserContextServiceAwareInterface
{
    use UserContextServiceAwareTrait;

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->structure = $context['structure'];
    }

    /**
     * @param string $privilege
     * @return boolean
     * @throws FailedAssertionException
     */
    public function assert($privilege = null)
    {
        switch($privilege) {
            case StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES :
                return $this->isSesStructures();
            case StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES :
                return $this->isSesStructures();
        }
        return true;
    }


    /**
     * @return bool
     */
    protected function isSesStructures()
    {
        $connectedRole = $this->userContextService->getSelectedIdentityRole();
        switch($connectedRole->getCode()) {
            case Role::CODE_ADMIN_TECH : return true;
        }
        return true;
    }
}