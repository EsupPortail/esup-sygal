<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Module implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $libelle;

    /** @var string|null */
    private $description;

    /** @var Collection (Formation) */
    private $formations;

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
     * @return Collection
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

}