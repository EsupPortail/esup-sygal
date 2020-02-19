<?php

namespace ComiteSuivi\Entity\Db;

use Application\Entity\Db\These;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class ComiteSuivi {
    use HistoriqueAwareTrait;

    /** @var integer */
    private $id;
    /** @var These */
    private $these;
    /** @var DateTime */
    private $dateComite;
    /** @var string */
    private $anneeThese;
    /** @var string */
    private $anneeScolaire;
    /** @var ArrayCollection (Membre) */
    private $membres;
    /** @var ArrayCollection (CompteRendu) */
    private $compterendus;

    //$validations;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ComiteSuivi
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return ComiteSuivi
     */
    public function setThese($these)
    {
        $this->these = $these;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateComite()
    {
        return $this->dateComite;
    }

    /**
     * @param DateTime $dateComite
     * @return ComiteSuivi
     */
    public function setDateComite($dateComite)
    {
        $this->dateComite = $dateComite;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnneeThese()
    {
        return $this->anneeThese;
    }

    /**
     * @param string $anneeThese
     * @return ComiteSuivi
     */
    public function setAnneeThese($anneeThese)
    {
        $this->anneeThese = $anneeThese;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnneeScolaire()
    {
        return $this->anneeScolaire;
    }

    /**
     * @param string $anneeScolaire
     * @return ComiteSuivi
     */
    public function setAnneeScolaire($anneeScolaire)
    {
        $this->anneeScolaire = $anneeScolaire;
        return $this;
    }

    /**
     * @return array
     */
    public function getMembres()
    {
        return $this->membres->toArray();
    }



}