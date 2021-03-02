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
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Membre
     */
    public function setId(int $id) : Membre
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ComiteSuivi|null
     */
    public function getComite() : ?ComiteSuivi
    {
        return $this->comite;
    }

    /**
     * @param ComiteSuivi|null $comite
     * @return Membre
     */
    public function setComite(?ComiteSuivi $comite)  : Membre
    {
        $this->comite = $comite;
        return $this;
    }

    /**
     * @return Individu|null
     */
    public function getIndividu() : ?Individu
    {
        return $this->individu;
    }

    /**
     * @param Individu|null $individu
     * @return Membre
     */
    public function setIndividu(?Individu $individu) : Membre
    {
        $this->individu = $individu;
        return $this;
    }

    /**
     * @return Role|null
     */
    public function getRole() : ?Role
    {
        return $this->role;
    }

    /**
     * @param Role|null $role
     * @return Membre
     */
    public function setRole(?Role $role) : Membre
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom() : string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     * @return Membre
     */
    public function setPrenom(?string $prenom) : Membre
    {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNom() : ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return Membre
     */
    public function setNom(?string $nom) : Membre
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEtablissement() : ?string
    {
        return $this->etablissement;
    }

    /**
     * @param string|null $etablissement
     * @return Membre
     */
    public function setEtablissement(?string $etablissement) : Membre
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return Membre
     */
    public function setEmail(?string $email) : Membre
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getDenomination() : string
    {
        return $this->prenom." ".$this->nom;
    }

}