<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use UnicaenPrivilege\Entity\Db\AbstractPrivilege;
use UnicaenUtilisateur\Entity\Db\RoleInterface;

class Privilege extends AbstractPrivilege
{
    /** @var ArrayCollection */
    private $profils;

    public function __construct()
    {
        parent::__construct();
        $this->profils = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getProfils()
    {
        return $this->profils;
    }

    /**
     * @param Profil $profil
     * @return Privilege
     */
    public function addProfil($profil)
    {
        $this->profils->add($profil);
        return $this;
    }

    /**
     * @param Profil $profil
     * @return Privilege
     */
    public function removeProfil($profil)
    {
        $this->profils->removeElement($profil);
        return $this;
    }

    /**
     * @param Profil $profil
     * @return bool
     */
    public function hasProfil($profil)
    {
        return $this->profils->contains($profil);
    }

    /**
     * @deprecated
     */
    public function getRole()
    {
        throw new \BadMethodCallException("Cette méthode est remplacée par getRoles()");
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param RoleInterface $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->roles->contains($role);
    }
}
