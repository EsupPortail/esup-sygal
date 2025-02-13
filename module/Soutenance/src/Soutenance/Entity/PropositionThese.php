<?php

namespace Soutenance\Entity;

use These\Entity\Db\These;

class PropositionThese extends Proposition{

    private ?These $these = null;

    private ?string $titre = null;
    private ?string $nouveauTitre = null;
    private bool $labelEuropeen = false;
    //    private bool $manuscritAnglais = false;

    public function __construct(?These $these = null)
    {
        parent::__construct();
        $this->setThese($these);
        $this->setLabelEuropeen(false);
        //        $this->setManuscritAnglais(false);
    }

    /**
     * @return These|null
     */
    public function getThese(): ?These
    {
        return $this->these;
    }

    /**
     * @param These|null $these
     */
    public function setThese(?These $these): void
    {
        $this->these = $these;
    }

    /**
     * @return string|null
     */
    public function getTitre(): ?string
    {
        return $this->titre;
    }

    /**
     * @param string|null $titre
     */
    public function setTitre(?string $titre): void
    {
        $this->titre = $titre;
    }

    /**
     * @return string
     */
    public function getNouveauTitre()
    {
        return $this->nouveauTitre;
    }

    /**
     * @param string $nouveauTitre
     * @return Proposition
     */
    public function setNouveauTitre($nouveauTitre)
    {
        $this->nouveauTitre = $nouveauTitre;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLabelEuropeen()
    {
        return $this->labelEuropeen;
    }

    /**
     * @param bool $labelEuropeen
     * @return Proposition
     */
    public function setLabelEuropeen($labelEuropeen)
    {
        $this->labelEuropeen = $labelEuropeen;
        return $this;
    }

    //    /**
//     * @return bool
//     */
//    public function isManuscritAnglais()
//    {
//        return $this->manuscritAnglais;
//    }
//
//    /**
//     * @param bool $manuscritAnglais
//     * @return Proposition
//     */
//    public function setManuscritAnglais($manuscritAnglais)
//    {
//        $this->manuscritAnglais = $manuscritAnglais;
//        return $this;
//    }
}