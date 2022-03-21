<?php

namespace Application\Entity\Db;

use DateTime;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Financement {

    use HistoriqueAwareTrait;

    /** @var int */
    private $id;
    /** @var Source */
    private $source;
    /** @var string */
    private $sourceCode;
    /** @var These */
    private $these;
    /** @var int */
    private $annee;
    /** @var OrigineFinancement */
    private $origineFinancement;
    /** @var string */
    private $complementFinancement;
    /** @var string */
    private $quotiteFinancement;
    /** @var DateTime */
    private $dateDebut;
    /** @var DateTime */
    private $dateFin;
    /** @var string */
    protected $codeTypeFinancement;
    /** @var string */
    protected $libelleTypeFinancement;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Source $source
     * @return Financement
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @param string $sourceCode
     * @return Financement
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;
        return $this;
    }

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return Financement
     */
    public function setThese($these)
    {
        $this->these = $these;
        return $this;
    }

    /**
     * @return int
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * @param int $annee
     * @return Financement
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;
        return $this;
    }

    /**
     * @return OrigineFinancement
     */
    public function getOrigineFinancement()
    {
        return $this->origineFinancement;
    }

    /**
     * @param OrigineFinancement $origineFinancement
     * @return Financement
     */
    public function setOrigineFinancement($origineFinancement)
    {
        $this->origineFinancement = $origineFinancement;
        return $this;
    }

    /**
     * @return string
     */
    public function getComplementFinancement()
    {
        return $this->complementFinancement;
    }

    /**
     * @param string $complementFinancement
     * @return Financement
     */
    public function setComplementFinancement($complementFinancement)
    {
        $this->complementFinancement = $complementFinancement;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuotiteFinancement()
    {
        return $this->quotiteFinancement;
    }

    /**
     * @param string $quotiteFinancement
     * @return Financement
     */
    public function setQuotiteFinancement($quotiteFinancement)
    {
        $this->quotiteFinancement = $quotiteFinancement;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * @param DateTime $dateDebut
     * @return Financement
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * @param DateTime $dateFin
     * @return Financement
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeTypeFinancement()
    {
        return $this->codeTypeFinancement;
    }

    /**
     * @param string $codeTypeFinancement
     * @return Financement
     */
    public function setCodeTypeFinancement($codeTypeFinancement): Financement
    {
        $this->codeTypeFinancement = $codeTypeFinancement;

        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleTypeFinancement()
    {
        return $this->libelleTypeFinancement;
    }

    /**
     * @param string $libelleTypeFinancement
     * @return Financement
     */
    public function setLibelleTypeFinancement($libelleTypeFinancement): Financement
    {
        $this->libelleTypeFinancement = $libelleTypeFinancement;

        return $this;
    }
}