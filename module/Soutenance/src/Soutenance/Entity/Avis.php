<?php

namespace Soutenance\Entity;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\These;
use Application\Entity\Db\Validation;

class Avis {

    /** @var int */
    private $id;
    /** @var These */
    private $these;
    /** @var Acteur */
    private $rapporteur;
    /** @var string */
    private $avis;
    /** @var string */
    private $motif;
    /** @var Validation */
    private $validation;
    /** @var Fichier */
    private $fichier;

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
     * @return Avis
     */
    public function setThese($these)
    {
        $this->these = $these;
        return $this;
    }

    /**
     * @return Acteur
     */
    public function getRapporteur()
    {
        return $this->rapporteur;
    }

    /**
     * @param Acteur $rapporteur
     * @return Avis
     */
    public function setRapporteur($rapporteur)
    {
        $this->rapporteur = $rapporteur;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvis()
    {
        return $this->avis;
    }

    /**
     * @param string $avis
     * @return Avis
     */
    public function setAvis($avis)
    {
        $this->avis = $avis;
        return $this;
    }

    /**
     * @return string
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * @param string $motif
     * @return Avis
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;
        return $this;
    }

    /**
     * @return Validation
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param Validation $validation
     * @return Avis
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
        return $this;
    }

    /**
     * @return Fichier
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * @param Fichier $fichier
     * @return Avis
     */
    public function setFichier($fichier)
    {
        $this->fichier = $fichier;
        return $this;
    }
}

?>