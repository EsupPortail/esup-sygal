<?php

namespace Application\Service\Role;

trait RoleServiceAwareTrait
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * @return RoleService
     */
    public function getRoleService()
    {
        return $this->roleService;
    }

    /**
     * @param RoleService $roleService
     */
    public function setRoleService(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }
}