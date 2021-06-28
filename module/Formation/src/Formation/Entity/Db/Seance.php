<?php

namespace Formation\Entity\Db;

use DateTime;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Seance implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;

    /** @var Session|null */
    private $session;

    /** @var DateTime */
    private $debut;

    /** @var DateTime */
    private $fin;

    /** @var string */
    private $lieu;

    /** @var string */
    private $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @param Session|null $session
     * @return Seance
     */
    public function setSession(?Session $session): Seance
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDebut(): DateTime
    {
        return $this->debut;
    }

    /**
     * @param DateTime $debut
     * @return Seance
     */
    public function setDebut(DateTime $debut): Seance
    {
        $this->debut = $debut;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFin(): DateTime
    {
        return $this->fin;
    }

    /**
     * @param DateTime $fin
     * @return Seance
     */
    public function setFin(DateTime $fin): Seance
    {
        $this->fin = $fin;
        return $this;
    }

    /**
     * @return string
     */
    public function getLieu(): string
    {
        return $this->lieu;
    }

    /**
     * @param string $lieu
     * @return Seance
     */
    public function setLieu(string $lieu): Seance
    {
        $this->lieu = $lieu;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Seance
     */
    public function setDescription(string $description): Seance
    {
        $this->description = $description;
        return $this;
    }

}