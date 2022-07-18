<?php

namespace Formation\Entity\Db;

use DateTime;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Seance implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    private int $id;
    private ?Session $session = null;
    private ?DateTime $debut = null;
    private ?DateTime $fin = null;
    private ?string $lieu = null;
    private ?string $description = null;

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
     * @return DateTime|null
     */
    public function getDebut(): ?DateTime
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
     * @return DateTime|null
     */
    public function getFin(): ?DateTime
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
     * @return string|null
     */
    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    /**
     * @param string|null $lieu
     * @return Seance|null
     */
    public function setLieu(?string $lieu): Seance
    {
        $this->lieu = $lieu;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Seance
     */
    public function setDescription(?string $description): Seance
    {
        $this->description = $description;
        return $this;
    }

    public function getDuree() : float
    {
        $somme = new DateTime('00:00');
        $debut = $this->getDebut();
        $fin = $this->getFin();
        $interval = $debut->diff($fin);
        $somme->add($interval);
        $interval = $somme->diff(new DateTime('00:00'));
        return ((float) $interval->format('%h')) + ((float) $interval->format('%i'))/60;
    }
}