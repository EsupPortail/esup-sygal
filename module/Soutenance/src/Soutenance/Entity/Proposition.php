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

    /** @var ArrayCollection */
    private $membres;
//    /** @var ArrayCollection */
//    private $validations;

    /**
     * Proposition constructor.
     */
    public function __construct()
    {
        $this->membres = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * @param ArrayCollection $validations
     * @return Proposition
     */
    public function setValidations($validations)
    {
        $this->validations = $validations;
        return $this;
    }


    /** fonction metier qui crée un tableau associatif des indicateurs*/
    public function computeIndicateur() {
        $nbMembre       = 0;
        $nbFemme        = 0;
        $nbHomme        = 0;
        $nbRangA        = 0;
        $nbExterieur    = 0;
        $nbRapporteur   = 0;

        /** @var Membre $membre */
        foreach ($this->getMembres() as $membre) {
            $nbMembre++;
            if ($membre->getGenre() === "F") $nbFemme++; else $nbHomme++;
            if ($membre->getRang() === "A") $nbRangA++;
            if ($membre->getExterieur() === "oui") $nbExterieur++;
            if ($membre->getRole() === "Rapporteur") $nbRapporteur++;
        }

        $indicateurs = [];

        /**  Il faut essayer de maintenir la parité Homme/Femme*/
        $ratioFemme = $nbFemme / $nbMembre;
        $ratioHomme = 1 - $ratioFemme;
        $indicateurs["parité"]      = ["Femme" => $ratioFemme, "Homme" => $ratioHomme];
        if ($ratioFemme < 0.2 OR $ratioHomme < 0.2) {
            $indicateurs["parité"]["valide"]    = false;
        } else {
            $indicateurs["parité"]["valide"]    = true;
        }

        /** Au moins deux rapporteurs */
        $indicateurs["rapporteur"]      = ["Nombre" => $nbRapporteur, "Ratio" => $nbRapporteur/$nbMembre];
        if ($nbRapporteur < 2) {
            $indicateurs["rapporteur"]["valide"]    = false;
        } else {
            $indicateurs["rapporteur"]["valide"]    = true;
        }

        /** Au moins la motié du jury de rang A */
        $ratioRangA = $nbRangA / $nbMembre;
        $indicateurs["rang A"]      = ["Nombre" => $nbRangA, "Ratio" => $ratioRangA];
        if ($ratioRangA < 0.5)  {
            $indicateurs["rang A"]["valide"]    = false;
        } else {
            $indicateurs["rang A"]["valide"]    = true;
        }

        /** Au moins la motié du jury exterieur*/
        $ratioExterieur = $nbExterieur / $nbMembre;
        $indicateurs["exterieur"]      = ["Nombre" => $nbExterieur, "Ratio" => $ratioExterieur];
        if ($ratioRangA < 0.5)  {
            $indicateurs["exterieur"]["valide"]    = false;
        } else {
            $indicateurs["exterieur"]["valide"]    = true;
        }

        $valide = $indicateurs["parité"]["valide"] && $indicateurs["rapporteur"]["valide"]
            && $indicateurs["rang A"]["valide"] && $indicateurs["exterieur"]["valide"];

        $indicateurs["valide"] = $valide;

        return $indicateurs;

    }

}