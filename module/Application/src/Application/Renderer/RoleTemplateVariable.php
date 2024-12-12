<?php

namespace Application\Renderer;

use Application\Entity\Db\Role;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class RoleTemplateVariable extends AbstractTemplateVariable
{
    private Role $role;

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelle() : string
    {
        return $this->role->getLibelle();
    }
}