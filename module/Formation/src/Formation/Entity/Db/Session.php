<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Session implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;

    /** @var Formation|null */
    private $formation;

    /** @var Collection (Seance) */
    private $seances;

    /** @var string|null */
    private $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Formation|null
     */
    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    /**
     * @param Formation|null $formation
     * @return Session
     */
    public function setFormation(?Formation $formation): Session
    {
        $this->formation = $formation;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSeances() : Collection
    {
        return $this->seances;
    }

    /**
     * @param Seance $seance
     * @return bool
     */
    public function hasSeance(Seance $seance) : bool
    {
        return $this->seances->contains($seance);
    }

    /**
     * @param Seance $seance
     * @return Session
     */
    public function addSeance(Seance $seance) : Session
    {
        if (! $this->hasSeance($seance)) $this->seances->add($seance);
        return $this;
    }

    /**
     * @param Seance $seance
     * @return $this
     */
    public function removeSeance(Seance $seance) : Session
    {
        $this->seances->removeElement($seance);
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
     * @return Session
     */
    public function setDescription(?string $description): Session
    {
        $this->description = $description;
        return $this;
    }
}