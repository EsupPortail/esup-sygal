<?php

namespace Soutenance\Entity;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\These;
use Application\Entity\Db\Validation;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Avis {
    use HistoriqueAwareTrait;

    const FAVORABLE = 'Favorable';
    const DEFAVORABLE = 'Défavorable';

    /** @var int */
    private $id;
    /** @var Proposition */
    private  $proposition;
    /** @var Membre */
    private $membre;
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
        return $this->proposition->getThese();
    }

    /**
     * @return Acteur
     */
    public function getRapporteur()
    {
        return $this->getMembre()->getActeur();
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

    /**
     * @return Proposition
     */
    public function getProposition()
    {
        return $this->proposition;
    }

    /**
     * @param Proposition $proposition
     * @return Avis
     */
    public function setProposition($proposition)
    {
        $this->proposition = $proposition;
        return $this;
    }

    /**
     * @return Membre
     */
    public function getMembre()
    {
        return $this->membre;
    }

    /**
     * @param Membre $membre
     * @return Avis
     */
    public function setMembre($membre)
    {
        $this->membre = $membre;
        return $this;
    }
}