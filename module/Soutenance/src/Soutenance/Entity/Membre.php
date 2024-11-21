<?php

namespace Soutenance\Entity;

use These\Entity\Db\Acteur;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\Role;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class Membre implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

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
    private $adresse;
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
    public function getId() : ?int
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
     * @return string|null
     */
    public function getCivilite(): string|null
    {
        $civilite = null;
        if ($this->getIndividu()) {
            if ($this->getIndividu()->getCivilite() === 'M.') $civilite = "Monsieur";
            if ($this->getIndividu()->getCivilite() === 'Mme') $civilite = "Madame";
        }

        if(empty($civilite)){
            if ($this->getGenre() === 'F') $civilite = "Madame";
            if ($this->getGenre() === 'H') $civilite = "Monsieur";
        }

        return $civilite;
    }

    /**
     * @return string
     */
    public function getDenomination()
    {
        return (($this->getGenre()==='F')?"Mme":"M").' '.$this->prenom." ".strtoupper($this->getNom());
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
     * @param Qualite|null $qualite
     * @return Membre
     */
    public function setQualite(?Qualite $qualite) : Membre
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
    public function getEtablissement(): ?string
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
     * @return string
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * @param string $adresse
     * @return Membre
     */
    public function setAdresse(string $adresse): Membre
    {
        $this->adresse = $adresse;
        return $this;
    }



    /**
     * @return string
     */
    public function getExterieur() : ?string
    {
        return $this->exterieur;
    }

    /**
     * @param string $exterieur
     * @return Membre
     */
    public function setExterieur(string $exterieur) : Membre
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
     * @return Acteur|null
     */
    public function getActeur(): ?Acteur
    {
        return $this->acteur;
    }

    public function setActeur(?Acteur $acteur) : Membre
    {
        $this->acteur = $acteur;
        return $this;
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        $acteur = $this->getActeur();
        if ($acteur === null) return null;
        return $acteur->getIndividu();
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

    public function estMembre() {
        switch ($this->getRole()) {
            case Membre::RAPPORTEUR_JURY :
            case Membre::RAPPORTEUR_VISIO :
            case Membre::MEMBRE_JURY :
                return true;
        }
        return false;
    }

    public function estDirecteur()
    {
        if (!$this->getActeur()) return false;
        return (
            $this->getActeur()->getRole()->getCode() === Role::CODE_DIRECTEUR_THESE
        );
    }

    public function estCoDirecteur()
    {
        if (!$this->getActeur()) return false;
        return (
            $this->getActeur()->getRole()->getCode() === Role::CODE_CODIRECTEUR_THESE
        );
    }
}