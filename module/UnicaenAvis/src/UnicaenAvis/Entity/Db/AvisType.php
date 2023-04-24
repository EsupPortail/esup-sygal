<?php

namespace UnicaenAvis\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AvisType
 */
class AvisType
{
    /**
     * @var string
     */
    private string $code;

    /**
     * @var string
     */
    private string $libelle;

    /**
     * @var string|null
     */
    private ?string $description;

    /**
     * @var int
     */
    private int $ordre;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private Collection $avisTypeValeurs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private Collection $avisValeurs;


    public function __construct()
    {
        $this->avisTypeValeurs = new ArrayCollection();
        $this->avisValeurs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getLibelle();
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return AvisType
     */
    public function setCode(string $code): AvisType
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return AvisType
     */
    public function setLibelle(string $libelle): AvisType
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return AvisType
     */
    public function setDescription($description = null): AvisType
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param int $ordre
     * @return self
     */
    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrdre(): int
    {
        return $this->ordre;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return AvisTypeValeur[]|\Doctrine\Common\Collections\Collection
     */
    public function getAvisTypeValeurs(): Collection
    {
        return $this->avisTypeValeurs;
    }

    /**
     * @return AvisValeur[]|\Doctrine\Common\Collections\Collection
     */
    public function getAvisValeurs(): Collection
    {
        return $this->avisValeurs;
    }

    public function addAvisValeurs(Collection $avisValeurs)
    {
        foreach ($avisValeurs as $avisValeur) {
            $this->avisValeurs->add($avisValeur);
        }
    }

    public function removeAvisValeurs(Collection $avisValeurs)
    {
        foreach ($avisValeurs as $avisValeur) {
            $this->avisValeurs->removeElement($avisValeur);
        }
    }
}
