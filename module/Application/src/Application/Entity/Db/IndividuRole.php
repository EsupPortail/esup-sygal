<?php

namespace Application\Entity\Db;

class IndividuRole
{
    /**
     * @var int $id
     * @var Individu $individu
     * @var Role $role
     */
    private $id;
    protected $individu;
    protected $role;

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /** @return Individu */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return IndividuRole
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;
        return $this;
    }

    /** @return Role */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param Role $role
     * @return IndividuRole
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

}