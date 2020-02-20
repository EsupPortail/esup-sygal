<?php

namespace ComiteSuivi\Entity\Db;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Doctrine\Common\Collections\ArrayCollection;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Membre {
    use HistoriqueAwareTrait;

    const ROLE_EXAMINATEUR_CODE = 'CST_E';
    const ROLE_OBSERVATEUR_CODE = 'CST_O';

    /** @var integer */
    private $id;
    /** @var ComiteSuivi */
    private $comite;
    /** @var Individu */
    private $individu;
    /** @var Role */
    private $role;
    /** @var string */
    private $prenom;
    /** @var string */
    private $nom;
    /** @var string */
    private $etablissement;
    /** @var string */
    private $email;

    /** @var ArrayCollection (CompteRendu) */
    private $compterendus;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Membre
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ComiteSuivi
     */
    public function getComite()
    {
        return $this->comite;
    }

    /**
     * @param ComiteSuivi $comite
     * @return Membre
     */
    public function setComite($comite)
    {
        $this->comite = $comite;
        return $this;
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return Membre
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;
        return $this;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param Role $role
     * @return Membre
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     * @return Membre
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return Membre
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return string
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param string $etablissement
     * @return Membre
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Membre
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getDenomination()
    {
        return $this->prenom." ".$this->nom;
    }

}