<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;

class RoleModele
{
    /** @var int $id */
    private $id;
    /** @var string $libelle */
    protected $libelle;
    /** @var TypeStructure $structureType */
    protected $structureType;
    /** @var string $roleCode */
    protected $roleCode;
    /** @var ArrayCollection */
    protected $privileges;

    public function __construct()
    {
        $this->privileges = new ArrayCollection();
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
     * @param Privilege $privilege
     * @return RoleModele
     */
    public function addPrivilege($privilege)
    {
        $this->privileges->add($privilege);
        return $this;
    }

    /**
     * @param Privilege $privilege
     * @return RoleModele
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

}