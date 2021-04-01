<?php

namespace Application\Entity\Db;

use Application\Entity\Db\Interfaces\IndividuAwareInterface;
use BjyAuthorize\Provider\Role\ProviderInterface;
use UnicaenApp\Entity\UserInterface;
use UnicaenAuth\Entity\Db\AbstractUser;

/**
 * Classe Utilisateur.
 *
 * NB: hérite de AbstractUser uniquement pour pouvoir utiliser HistoriqueListener.
 */
class Utilisateur extends AbstractUser implements UserInterface, ProviderInterface, IndividuAwareInterface
{
    const APP_UTILISATEUR_ID = 1; // indispensable à UnicaenImport !
    const APP_UTILISATEUR_USERNAME = 'sygal-app';

    /**
     * @var Individu
     */
    protected $individu;

    /**
     * @var Role
     */
    protected $lastRole;

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return self
     */
    public function setIndividu(?Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

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