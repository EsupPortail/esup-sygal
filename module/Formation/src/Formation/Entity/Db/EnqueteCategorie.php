<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class EnqueteCategorie implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;
    /** @var string|null */
    private $libelle;
    /** @var string|null */
    private $description;
    /** @var int|null */
    private $ordre;
    /** @var Collection (EnqueteQuestion) */
    private $questions;

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
     * @return EnqueteCategorie
     */
    public function setLibelle(?string $libelle): EnqueteCategorie
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
     * @return EnqueteCategorie
     */
    public function setDescription(?string $description): EnqueteCategorie
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    /**
     * @param int|null $ordre
     * @return EnqueteCategorie
     */
    public function setOrdre(?int $ordre): EnqueteCategorie
    {
        $this->ordre = $ordre;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }
}