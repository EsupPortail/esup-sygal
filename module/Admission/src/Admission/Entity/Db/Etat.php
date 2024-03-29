<?php

namespace Admission\Entity\Db;

class Etat {

    const CODE_EN_COURS_SAISIE   = 'C';
    const CODE_ABANDONNE = 'A';
    const CODE_VALIDE = 'V';
    const CODE_EN_COURS_VALIDATION = 'E';
    const CODE_REJETE = 'R';

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

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Etat
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Etat
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set icone.
     *
     * @param string|null $icone
     *
     * @return Etat
     */
    public function setIcone($icone = null)
    {
        $this->icone = $icone;

        return $this;
    }

    /**
     * Set couleur.
     *
     * @param string|null $couleur
     *
     * @return Etat
     */
    public function setCouleur($couleur = null)
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * Set ordre.
     *
     * @param int|null $ordre
     *
     * @return Etat
     */
    public function setOrdre($ordre = null)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Etat
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
