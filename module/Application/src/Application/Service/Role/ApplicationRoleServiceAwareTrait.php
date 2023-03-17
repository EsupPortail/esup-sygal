<?php

namespace Application\Service\Role;

trait ApplicationRoleServiceAwareTrait
{
    /**
     * @var RoleService
     */
    protected $applicationRoleService;

    /**
     * @return RoleService
     */
    public function getApplicationRoleService(): RoleService
    {
        return $this->applicationRoleService;
    }

    /**
     * @param RoleService $applicationRoleService
     */
    public function setApplicationRoleService(RoleService $applicationRoleService): void
    {
        $this->applicationRoleService = $applicationRoleService;
    }
}