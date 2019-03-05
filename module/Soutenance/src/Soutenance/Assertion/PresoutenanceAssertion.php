<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\UserContextServiceAwareTrait;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class PresoutenanceAssertion implements  AssertionInterface {
    use UserContextServiceAwareTrait;

    /**
     * !!!! Pour éviter l'erreur "Serialization of 'Closure' is not allowed"... !!!!
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    public function __invoke($page)
    {
        return true;
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        /** @var These $these */
        $these = $resource;

        switch ($privilege) {
            case PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU:
                $role = $this->userContextService->getSelectedIdentityRole();
                return ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure());
                break;
            case PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION:
                $role = $this->userContextService->getSelectedIdentityRole();
                return ($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure());
                break;
            case PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION:
                $role = $this->userContextService->getSelectedIdentityRole();
                return (($role->getCode() === Role::CODE_BDD && $role->getStructure() === $these->getEtablissement()->getStructure()) ||
                    $role->getCode() === Role::CODE_OBSERVATEUR ||
                    $role->getCode() === Role::CODE_ADMIN_TECH);
        }

        return false;
    }
}