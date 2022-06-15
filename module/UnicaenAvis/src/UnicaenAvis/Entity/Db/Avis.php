<?php

namespace UnicaenAvis\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Avis
 */
class Avis
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
     * @var \UnicaenAvis\Entity\Db\AvisValeur|null
     */
    private ?AvisValeur $avisValeur = null;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisComplem[]|\Doctrine\Common\Collections\Collection
     */
    private Collection $avisComplems;


    public function __construct()
    {
        $this->avisComplems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getAvisType() . " : " . $this->getAvisValeur()->getValeur();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set avisType.
     *
     * @param \UnicaenAvis\Entity\Db\AvisType|null $avisType
     *
     * @return Avis
     */
    public function setAvisType(\UnicaenAvis\Entity\Db\AvisType $avisType = null)
    {
        $this->avisType = $avisType;

        return $this;
    }

    /**
     * Get avisType.
     *
     * @return \UnicaenAvis\Entity\Db\AvisType|null
     */
    public function getAvisType()
    {
        return $this->avisType;
    }

    /**
     * Set avisValeur.
     *
     * @param \UnicaenAvis\Entity\Db\AvisValeur|null $avisValeur
     *
     * @return Avis
     */
    public function setAvisValeur(\UnicaenAvis\Entity\Db\AvisValeur $avisValeur = null)
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
     * @return string
     */
    public function getAvisComplemsToHtml(): string
    {
        /** @var \UnicaenAvis\Entity\Db\AvisComplem[] $avisComplems */
        $avisComplems = $this->avisComplems->toArray();
        if (empty($avisComplems)) {
            return '';
        }

        usort($avisComplems, [AvisComplem::class, 'sorterByOrdre']);

        $html = '';
        foreach ($avisComplems as $avisComplem) {
            $html .= '<p class="avis-complem">' . $avisComplem->getValeurToHtml() . '</p>';
        }

        return $html;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|\UnicaenAvis\Entity\Db\AvisComplem[]
     */
    public function getAvisComplems(): Collection
    {
        return $this->avisComplems;
    }

    public function addAvisComplems(iterable $avisComplems): Avis
    {
        foreach ($avisComplems as $avisComplem)
            $this->avisComplems->add($avisComplem);

        return $this;
    }

    public function removeAvisComplems(iterable $avisComplems): Avis
    {
        foreach ($avisComplems as $avisComplem)
            $this->avisComplems->removeElement($avisComplem);

        return $this;
    }
}
