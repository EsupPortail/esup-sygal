<?php

namespace Application\Form;

trait RoleFormAwareTrait {

    /** @var RoleForm  */
    private RoleForm $roleForm;

    /**
     * @return RoleForm
     */
    public function getRoleForm(): RoleForm
    {
        return $this->roleForm;
    }

    /**
     * @param RoleForm $roleForm
     */
    public function setRoleForm(RoleForm $roleForm): void
    {
        $this->roleForm = $roleForm;
    }
}