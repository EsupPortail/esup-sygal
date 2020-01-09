<?php

namespace Soutenance\Entity;

use Application\Entity\Db\Acteur;
use UnicaenApp\Exception\RuntimeException;

class Membre {

    const MEMBRE_JURY        = 'Membre';
    const RAPPORTEUR_JURY    = 'Rapporteur membre du jury';
    const RAPPORTEUR_VISIO   = 'Rapporteur en visioconférence';
    const RAPPORTEUR_ABSENT  = 'Rapporteur absent';

    /** @var int */
    private $id;
    /** @var Proposition */
    private $proposition;
    /** @var string */
    private $genre;
    /** @var string */
    private $prenom;
    /** @var string */
    private $nom;
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
    /** @var boolean */
    private $visio;

    /** @var Acteur */
    private  $acteur;

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
        return $this->prenom." ".$this->getNom();
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
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
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
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
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
        if ($this->getQualite() === null) {
            throw new RuntimeException("Pas de qualité associé au membre de jury [".$this->getDenomination()."].");
        }
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
     * @return bool|null
     */
    public function isExterieur()
    {
        if ($this->exterieur === null) return null;
        return ($this->exterieur === "oui");
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
     * @return Acteur
     */
    public function getActeur()
    {
        return $this->acteur;
    }

    /**
     * @param Acteur $acteur
     * @return Membre
     */
    public function setActeur($acteur)
    {
        $this->acteur = $acteur;
        return $this;
    }

    /** @return boolean */
    public function estRapporteur()
    {
        return $this->getRole() === Membre::RAPPORTEUR_JURY ||
            $this->getRole() === Membre::RAPPORTEUR_VISIO  ||
            $this->getRole() === Membre::RAPPORTEUR_ABSENT;
    }

    /**
     * @return bool
     */
    public function isVisio()
    {
        return $this->visio;
    }

    /**
     * @param bool $visio
     * @return Membre
     */
    public function setVisio($visio)
    {
        $this->visio = $visio;
        return $this;
    }
}