<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Module implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    private int $id;
    private ?string $libelle = null;
    private ?string $description = null;
    private ?string $lien = null;
    private Collection $formations;
    private bool $requireMissionEnseignement = false;

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

    public function getCode() : string
    {
        return 'M'.$this->getId();
    }

    public function isRequireMissionEnseignement(): bool
    {
        return $this->requireMissionEnseignement;
    }

    public function setRequireMissionEnseignement(bool $requireMissionEnseignement): void
    {
        $this->requireMissionEnseignement = $requireMissionEnseignement;
    }


}