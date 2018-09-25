<?php

namespace Soutenance\Entity;

use Application\Entity\Db\Individu;
use DateTime;

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

    /** @var Individu */
    private  $individu;


    /** @var string */
    private $persopass;
    /** @var boolean */
    private $nouveau;
    /** @var DateTime */
    private $expertise;

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

    /**
     * @return string
     */
    public function getPersopass()
    {
        return $this->persopass;
    }

    /**
     * @param string $persopass
     * @return Membre
     */
    public function setPersopass($persopass)
    {
        $this->persopass = $persopass;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNouveau()
    {
        return $this->nouveau;
    }

    /**
     * @param bool $nouveau
     * @return Membre
     */
    public function setNouveau($nouveau)
    {
        $this->nouveau = $nouveau;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpertise()
    {
        return $this->expertise;
    }

    /**
     * @param DateTime $expertise
     * @return Membre
     */
    public function setExpertise($expertise)
    {
        $this->expertise = $expertise;
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
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;
    }



}

?>