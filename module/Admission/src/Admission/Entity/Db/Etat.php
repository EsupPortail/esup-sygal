<?php

namespace Admission\Entity\Db;

class Etat {

    const CODE_EN_COURS   = 'C';
    const CODE_ABANDONNE = 'A';
    const CODE_VALIDE = 'V';

    private ?string $code = null;
    private ?string $libelle = null;
    private ?string $description = null;
    private ?string $icone = null;
    private ?string $couleur = null;
    private ?int $ordre = null;

    public function __toString(): string
    {
        return $this->libelle;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getIcone(): ?string
    {
        return $this->icone;
    }

    /**
     * @return string|null
     */
    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    /**
     * @return int|null
     */
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }
}