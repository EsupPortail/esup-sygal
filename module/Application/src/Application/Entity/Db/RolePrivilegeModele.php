<?php

namespace Application\Entity\Db;

class RolePrivilegeModele
{
    /** @var string $role */
    protected $roleCode;
    /** @var Privilege $privilege */
    protected $privilege;

    /**
     * @return string
     */
    public function getRoleCode()
    {
        return $this->roleCode;
    }

    /**
     * @return Privilege
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

}