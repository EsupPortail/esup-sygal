<?php

namespace Application\Entity\Db;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcoleDoctoraleIndividu
 */
class EcoleDoctoraleIndividu
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var EcoleDoctorale
     */
    private $ecole;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * @var Role
     */
    private $role;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return EcoleDoctorale
     */
    public function getEcole()
    {
        return $this->ecole;
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param EcoleDoctorale $ecole
     * @return EcoleDoctoraleIndividu
     */
    public function setEcole($ecole)
    {
        $this->ecole = $ecole;

        return $this;
    }

    /**
     * @param Individu $individu
     * @return EcoleDoctoraleIndividu
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * @param Role $role
     * @return EcoleDoctoraleIndividu
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }


}