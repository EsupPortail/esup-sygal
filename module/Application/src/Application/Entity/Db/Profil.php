<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Structure\Entity\Db\TypeStructure;

class Profil
{
    /** @var integer $id */
    private $id;
    /** @var string $libelle */
    protected $libelle;
    /** @var TypeStructure $structureType */
    protected $structureType;
    /** @var string $roleCode */
    protected $roleCode;
    /** @var string $description */
    protected $description;
    /** @var integer $ordre */
    private $ordre;
    /** @var ArrayCollection */
    protected $privileges;
    /** @var ArrayCollection */
    protected $roles;

    public function __construct()
    {
        $this->privileges = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    /**
     * @return TypeStructure
     */
    public function getStructureType()
    {
        return $this->structureType;
    }

    /**
     * @param TypeStructure $structureType
     */
    public function setStructureType($structureType)
    {
        $this->structureType = $structureType;
    }

    /**
     * @return string
     */
    public function getRoleCode()
    {
        return $this->roleCode;
    }

    /**
     * @param string $roleCode
     */
    public function setRoleCode($roleCode)
    {
        $this->roleCode = $roleCode;
    }

    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * @return ArrayCollection
     */
    public function getPrivileges()
    {
        return $this->privileges;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Profil
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @param int $ordre
     * @return Profil
     */
    public function setOrdre(int $ordre)
    {
        $this->ordre = $ordre;
        return $this;
    }

    /**
     * @param Privilege $privilege
     * @return Profil
     */
    public function addPrivilege($privilege)
    {
        $this->privileges->add($privilege);
        return $this;
    }

    /**
     * @param Privilege $privilege
     * @return Profil
     */
    public function removePrivilege($privilege)
    {
        $this->privileges->removeElement($privilege);
        return $this;
    }

    /**
     * @param Privilege $privilege
     * @return boolean
     */
    public function hasPrivilege($privilege)
    {
        return $this->privileges->contains($privilege);
    }


    /** @return ArrayCollection */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role $role
     * @return Profil
     */
    public function addRole($role)
    {
        $this->roles->add($role);
        return $this;
    }

    /**
     * @param Role $role
     * @return Profil
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);
        return $this;
    }

    /**
     * @param Role $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return $this->roles->contains($role);
    }
}