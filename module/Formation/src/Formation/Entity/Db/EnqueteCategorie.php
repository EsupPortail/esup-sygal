<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class EnqueteCategorie implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    private int $id;
    private ?string $libelle = null;
    private ?string $description = null;
    private ?int $ordre = null;
    private Collection $questions;

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