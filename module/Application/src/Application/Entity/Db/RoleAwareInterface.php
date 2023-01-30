<?php

namespace Application\Entity\Db;

interface RoleAwareInterface
{
    public function getRole(): Role;
    public function setRole(Role $role);
}