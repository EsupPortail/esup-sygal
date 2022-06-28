<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Module implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    private int $id;
    private ?string $libelle;
    private ?string $description;
    private ?string $lien;
    private Collection $formations;

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
     * @return Module
     */
    public function setLibelle(?string $libelle): Module
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
     * @return Module
     */
    public function setDescription(?string $description): Module
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
     * @return Module
     */
    public function setLien(?string $lien): Module
    {
        $this->lien = $lien;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

}