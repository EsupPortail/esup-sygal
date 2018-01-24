<?php

namespace Application\Entity\Db;

use Doctrine\ORM\Mapping as ORM;

/**
 * UniteRechercheIndividu
 */
class UniteRechercheIndividu
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;

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
     * @return UniteRecherche
     */
    public function getUniteRecherche()
    {
        return $this->uniteRecherche;
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
     * @param UniteRecherche $uniteRecherche
     * @return UniteRechercheIndividu
     */
    public function setUniteRecherche($uniteRecherche)
    {
        $this->uniteRecherche = $uniteRecherche;

        return $this;
    }

    /**
     * @param Individu $individu
     * @return UniteRechercheIndividu
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * @param Role $role
     * @return UniteRechercheIndividu
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

}