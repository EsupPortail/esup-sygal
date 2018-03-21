<?php

namespace Application\Entity\Db;

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





}