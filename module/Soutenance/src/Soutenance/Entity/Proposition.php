<?php

namespace Soutenance\Entity;

use Application\Entity\Db\These;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Proposition {

    /** @var int */
    private $id;
    /** @var These */
    private $these;
    /** @var DateTime */
    private $date;
    /** @var string */
    private $lieu;
    /** @var boolean */
    private $exterieur;

    /** @var ArrayCollection */
    private $membres;
    /** @var DateTime */
    private $renduRapport;

    /** @var DateTime */
    private $confidentialite;
    /** @var boolean */
    private $huitClos;
    /** @var boolean */
    private $labelEuropeen;
    /** @var boolean */
    private $manuscritAnglais;
    /** @var boolean */
    private $soutenanceAnglais;
    /** @var string */
    private $nouveauTitre;

//    /** @var ArrayCollection */
//    private $validations;

    /**
     * Proposition constructor.
     */
    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->setLabelEuropeen(false);
        $this->setManuscritAnglais(false);
        $this->setSoutenanceAnglais(false);
        $this->setHuitClos(false);
        $this->setExterieur(false);
//        $this->validations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return Proposition
     */
    public function setThese($these)
    {
        $this->these = $these;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return Proposition
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * @param string $lieu
     * @return Proposition
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExterieur()
    {
        return $this->exterieur;
    }

    /**
     * @param bool $exterieur
     * @return Proposition
     */
    public function setExterieur($exterieur)
    {
        $this->exterieur = $exterieur;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembres()
    {
        return $this->membres;
    }

    /**
     * @param ArrayCollection $membres
     * @return Proposition
     */
    public function setMembres($membres)
    {
        $this->membres = $membres;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getConfidentialite()
    {
        return $this->confidentialite;
    }

    /**
     * @param DateTime $confidentialite
     * @return Proposition
     */
    public function setConfidentialite($confidentialite)
    {
        $this->confidentialite = $confidentialite;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHuitClos()
    {
        return $this->huitClos;
    }

    /**
     * @param bool $huitClos
     * @return Proposition
     */
    public function setHuitClos($huitClos)
    {
        $this->huitClos = $huitClos;
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

    /**
     * @return bool
     */
    public function isManuscritAnglais()
    {
        return $this->manuscritAnglais;
    }

    /**
     * @param bool $manuscritAnglais
     * @return Proposition
     */
    public function setManuscritAnglais($manuscritAnglais)
    {
        $this->manuscritAnglais = $manuscritAnglais;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSoutenanceAnglais()
    {
        return $this->soutenanceAnglais;
    }

    /**
     * @param bool $soutenanceAnglais
     * @return Proposition
     */
    public function setSoutenanceAnglais($soutenanceAnglais)
    {
        $this->soutenanceAnglais = $soutenanceAnglais;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRenduRapport()
    {
        return $this->renduRapport;
    }

    /**
     * @param DateTime $renduRapport
     * @return Proposition
     */
    public function setRenduRapport($renduRapport)
    {
        $this->renduRapport = $renduRapport;
        return $this;
    }

    /** @return boolean */
    public function hasDateEtLieu()
    {
        return ($this->getDate() && $this->getLieu());
    }


    /**
     * @return Membre[]
     */
    public function getRapporteurs()
    {
        $rapporteurs = [];
        $membres = $this->getMembres();
        /** @var Membre $membre */
        foreach ($membres as $membre) {
            if ($membre->estRapporteur()) $rapporteurs[] = $membre;
        }

        return $rapporteurs;
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
}