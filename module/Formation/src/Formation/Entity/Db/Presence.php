<?php

namespace Formation\Entity\Db;

use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Presence implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    private int $id;
    private ?Inscription $inscription = null;
    private ?Seance $seance = null;
    private ?string $temoin = null;
    private ?string $description = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Presence
     */
    public function setId(int $id): Presence
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Inscription
     */
    public function getInscription(): Inscription
    {
        return $this->inscription;
    }

    /**
     * @param Inscription $inscription
     * @return Presence
     */
    public function setInscription(Inscription $inscription): Presence
    {
        $this->inscription = $inscription;
        return $this;
    }

    /**
     * @return Seance
     */
    public function getSeance(): Seance
    {
        return $this->seance;
    }

    /**
     * @param Seance $seance
     * @return Presence
     */
    public function setSeance(Seance $seance): Presence
    {
        $this->seance = $seance;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTemoin(): ?string
    {
        return $this->temoin;
    }

    /**
     * @param string|null $temoin
     * @return Presence
     */
    public function setTemoin(?string $temoin): Presence
    {
        $this->temoin = $temoin;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPresent() : bool
    {
        return $this->temoin === 'O';
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
     * @return Presence
     */
    public function setDescription(?string $description): Presence
    {
        $this->description = $description;
        return $this;
    }

}