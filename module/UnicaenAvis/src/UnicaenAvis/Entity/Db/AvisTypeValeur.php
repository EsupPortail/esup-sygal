<?php

namespace UnicaenAvis\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AvisTypeValeur
 */
class AvisTypeValeur
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisType
     */
    private AvisType $avisType;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisValeur
     */
    private AvisValeur $avisValeur;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private Collection $avisTypeValeurComplems;


    public function __construct()
    {
        $this->avisTypeValeurComplems = new ArrayCollection();
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
     * Set avisType.
     *
     * @param \UnicaenAvis\Entity\Db\AvisType|null $avisType
     *
     * @return AvisTypeValeur
     */
    public function setAvisType(\UnicaenAvis\Entity\Db\AvisType $avisType = null): AvisTypeValeur
    {
        $this->avisType = $avisType;

        return $this;
    }

    /**
     * Get avisType.
     *
     * @return \UnicaenAvis\Entity\Db\AvisType|null
     */
    public function getAvisType(): ?AvisType
    {
        return $this->avisType;
    }

    /**
     * Set avisValeur.
     *
     * @param \UnicaenAvis\Entity\Db\AvisValeur|null $avisValeur
     *
     * @return AvisTypeValeur
     */
    public function setAvisValeur(\UnicaenAvis\Entity\Db\AvisValeur $avisValeur = null): AvisTypeValeur
    {
        $this->avisValeur = $avisValeur;

        return $this;
    }

    /**
     * Get avisValeur.
     *
     * @return \UnicaenAvis\Entity\Db\AvisValeur|null
     */
    public function getAvisValeur(): ?AvisValeur
    {
        return $this->avisValeur;
    }

    /**
     * @return AvisTypeValeurComplem[]|\Doctrine\Common\Collections\Collection
     */
    public function getAvisTypeValeurComplems(): Collection
    {
        return $this->avisTypeValeurComplems;
    }
}
