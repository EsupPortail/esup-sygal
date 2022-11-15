<?php

namespace ComiteSuiviIndividuel\Entity\Db;

use Soutenance\Entity\Qualite;
use These\Entity\Db\Acteur;
use Individu\Entity\Db\Individu;
use These\Entity\Db\These;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class Membre implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const MEMBRE_CSI        = 'Membre';
    const RAPPORTEUR_CSI    = 'Rapporteur';

    private ?int $id = -1;
    private ?These $these = null;
    private ?string $genre = null;
    private ?string $prenom = null;
    private ?string $nom = null;
    private ?string $email = null;
    private ?Qualite $qualite = null;
    private ?string $etablissement = null;
    private ?string $exterieur = null;
    private ?string $role;
    private bool $visio = false;
    private ?Acteur $acteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getThese(): ?These
    {
        return $this->these;
    }

    public function setThese(?These $these): void
    {
        $this->these = $these;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): void
    {
        $this->genre = $genre;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getQualite(): ?Qualite
    {
        return $this->qualite;
    }

    public function setQualite(?Qualite $qualite): void
    {
        $this->qualite = $qualite;
    }

    public function getEtablissement(): ?string
    {
        return $this->etablissement;
    }

    public function setEtablissement(?string $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    public function getExterieur(): ?string
    {
        return $this->exterieur;
    }

    public function setExterieur(?string $exterieur): void
    {
        $this->exterieur = $exterieur;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function isVisio(): bool
    {
        return $this->visio;
    }

    public function setVisio(bool $visio): void
    {
        $this->visio = $visio;
    }

    public function getActeur(): ?Acteur
    {
        return $this->acteur;
    }

    public function setActeur(?Acteur $acteur): void
    {
        $this->acteur = $acteur;
    }

    public function getDenomination() : string
    {
        return $this->prenom." ".$this->getNom();
    }

    public function getRang() : ?string
    {
        if ($this->getQualite() === null) {
            throw new RuntimeException("Pas de qualité associé au membre de jury [".$this->getDenomination()."].");
        }
        return $this->getQualite()->getRang();
    }

    public function isExterieur() : ?bool
    {
        if ($this->exterieur === null) return null;
        return ($this->exterieur === "oui");
    }

    public function getIndividu() : ?Individu
    {
        $acteur = $this->getActeur();
        if ($acteur === null) return null;
        return $acteur->getIndividu();
    }
}