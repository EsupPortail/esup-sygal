<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Formation implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $libelle;

    /** @var string|null */
    private $description;

    /** @var string|null */
    private $lien;

    /** @var Collection (Session) */
    private $sessions;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     * @return Formation
     */
    public function setLibelle(?string $libelle): Formation
    {
        $this->libelle = $libelle;
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
     * @return Formation
     */
    public function setDescription(?string $description): Formation
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLien(): ?string
    {
        return $this->lien;
    }

    /**
     * @param string|null $lien
     * @return Formation
     */
    public function setLien(?string $lien): Formation
    {
        $this->lien = $lien;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSessions() : Collection
    {
        return $this->sessions;
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function hasSession(Session $session) : bool
    {
        return $this->sessions->contains($session);
    }

    /**
     * @param Session $session
     * @return Formation
     */
    public function addSession(Session $session) : Formation
    {
        if (! $this->hasSession($session)) $this->sessions->add($session);
        return $this;
    }

    /**
     * @param Session $session
     * @return Formation
     */
    public function removeSession(Session $session) : Formation
    {
        $this->sessions->removeElement($session);
        return $this;
    }
}