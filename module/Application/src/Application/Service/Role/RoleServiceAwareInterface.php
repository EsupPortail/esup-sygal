<?php

namespace Application\Service\Role;

interface RoleServiceAwareInterface
{
    public function setRoleService(RoleService $roleService);
}