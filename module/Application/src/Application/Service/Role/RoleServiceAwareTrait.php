<?php

namespace Application\Service\Role;

trait RoleServiceAwareTrait
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * @param RoleService $roleService
     */
    public function setRoleService(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }
}