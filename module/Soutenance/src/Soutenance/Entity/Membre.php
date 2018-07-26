<?php

namespace Soutenance\Entity;

class Membre {

    /** @var int */
    private $id;
    /** @var Proposition */
    private $proposition;
    /** @var string */
    private $genre;
    /** @var string */
    private $denomination;
    /** @var string */
    private $email;
    /** @var Qualite */
    private $qualite;

    /** @var string */
    private $etablissement;
    /** @var string */
    private $exterieur;
    /** @var string */
    private $role;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Proposition
     */
    public function getProposition()
    {
        return $this->proposition;
    }

    /**
     * @param Proposition $proposition
     * @return Membre
     */
    public function setProposition($proposition)
    {
        $this->proposition = $proposition;
        return $this;
    }

    /**
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     * @return Membre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * @return string
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * @param string $denomination
     * @return Membre
     */
    public function setDenomination($denomination)
    {
        $this->denomination = $denomination;
        return $this;
    }

    /**
     * @return Qualite
     */
    public function getQualite()
    {
        return $this->qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Membre
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;
        return $this;
    }

    /**
     * @return string
     */
    public function getRang()
    {
        return $this->getQualite()->getRang();
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
    public function getExterieur()
    {
        return $this->exterieur;
    }

    /**
     * @param string $exterieur
     * @return Membre
     */
    public function setExterieur($exterieur)
    {
        $this->exterieur = $exterieur;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
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


}

?>