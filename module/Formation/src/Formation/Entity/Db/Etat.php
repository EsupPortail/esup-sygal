<?php

namespace Formation\Entity\Db;

class Etat {

    const CODE_PREPARATION = 'P';
    const CODE_OUVERTE = 'O';
    const CODE_FERME = 'F';
    const CODE_CLOTURER = 'C';
    const CODE_ANNULE = 'A';

    /** @var string */
    private $code;
    /** @var string */
    private $libelle;
    /** @var string|null */
    private $description;
    /** @var string|null */
    private $icone;
    /** @var string|null */
    private $couleur;

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

}