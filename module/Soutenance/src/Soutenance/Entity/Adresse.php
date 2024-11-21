<?php

namespace Soutenance\Entity;

use JetBrains\PhpStorm\Pure;
use These\Entity\Db\These;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Adresse implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    private ?int $id = null;
    private ?Proposition $proposition = null;
    private ?string $ligne1 = null; // Salle et batiment
    private ?string $ligne2 = null; // Numéro et voie
    private ?string $ligne3 = null; // compléments
    private ?string $ligne4 = null; // Code postal et ville

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getProposition(): ?Proposition
    {
        return $this->proposition;
    }

    public function setProposition(?Proposition $proposition): void
    {
        $this->proposition = $proposition;
    }

    public function getLigne1(): ?string
    {
        return $this->ligne1;
    }

    public function setLigne1(?string $ligne1): void
    {
        $this->ligne1 = $ligne1;
    }

    public function getLigne2(): ?string
    {
        return $this->ligne2;
    }

    public function setLigne2(?string $ligne2): void
    {
        $this->ligne2 = $ligne2;
    }

    public function getLigne3(): ?string
    {
        return $this->ligne3;
    }

    public function setLigne3(?string $ligne3): void
    {
        $this->ligne3 = $ligne3;
    }

    public function getLigne4(): ?string
    {
        return $this->ligne4;
    }

    public function setLigne4(?string $ligne4): void
    {
        $this->ligne4 = $ligne4;
    }

    /** Helpers *******************************************************************************************************/

    #[Pure] public function getAssociatedThese() : ?These
    {
        $proposition = $this->getProposition();
        if ($proposition === null) return null;
        return $proposition->getThese();
    }

    #[Pure] public function format(): string
    {
        $texte = "";
        if ($this->getLigne1()) $texte .= $this->getLigne1() . "<br>";
        if ($this->getLigne2()) $texte .= $this->getLigne2() . "<br>";
        if ($this->getLigne3()) $texte .= $this->getLigne3() . "<br>";
        if ($this->getLigne4()) $texte .= $this->getLigne4() . "<br>";
        return $texte;
    }
}