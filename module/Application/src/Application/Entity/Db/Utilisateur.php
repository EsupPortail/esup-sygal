<?php

namespace Application\Entity\Db;

use BjyAuthorize\Provider\Role\ProviderInterface;
use UnicaenAuth\Entity\Db\AbstractUser;
use ZfcUser\Entity\UserInterface;

/**
 * Classe Utilisateur.
 *
 * NB: hérite de AbstractUser uniquement pour pouvoir utiliser HistoriqueListener.
 */
class Utilisateur extends AbstractUser implements UserInterface, ProviderInterface
{
    const APP_UTILISATEUR_USERNAME = 'sygal-app';

    /**
     * @var Role
     */
    protected $lastRole;

    /**
     * @return Role|null
     */
    public function getLastRole()
    {
        return $this->lastRole;
    }

    /**
     * @param Role|null $lastRole
     * @return self
     */
    public function setLastRole(Role $lastRole = null)
    {
        $this->lastRole = $lastRole;

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);

        return $this;
    }
}