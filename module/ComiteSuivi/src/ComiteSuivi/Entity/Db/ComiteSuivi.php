<?php

namespace ComiteSuivi\Entity\Db;

use Application\Entity\Db\These;
use Application\Entity\Db\Validation;
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
    private $comptesrendus;
    /** @var Validation */
    private $finalisation;
    /** @var Validation */
    private $validation;

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ComiteSuivi
     */
    public function setId(int $id) : ComiteSuivi
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return These|null
     */
    public function getThese() : ?These
    {
        return $this->these;
    }

    /**
     * @param These|null $these
     * @return ComiteSuivi
     */
    public function setThese(?These $these) : ComiteSuivi
    {
        $this->these = $these;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateComite() : ?DateTime
    {
        return $this->dateComite;
    }

    /**
     * @param ?DateTime $dateComite
     * @return ComiteSuivi
     */
    public function setDateComite(?DateTime $dateComite) : ComiteSuivi
    {
        $this->dateComite = $dateComite;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getAnneeThese() : ?string
    {
        return $this->anneeThese;
    }

    /**
     * @param string|null $anneeThese
     * @return ComiteSuivi
     */
    public function setAnneeThese(?string $anneeThese) : ComiteSuivi
    {
        $this->anneeThese = $anneeThese;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnneeScolaire() : ?string
    {
        return $this->anneeScolaire;
    }

    /**
     * @param string|null $anneeScolaire
     * @return ComiteSuivi
     */
    public function setAnneeScolaire(?string $anneeScolaire) : ComiteSuivi
    {
        $this->anneeScolaire = $anneeScolaire;
        return $this;
    }

    /**
     * @return Membre[]
     */
    public function getMembres() : array
    {
        return $this->membres->toArray();
    }

    /**
     * @return CompteRendu[]
     */
    public function getComptesRendus() : array
    {
        return $this->comptesrendus->toArray();
    }

    /**
     * @return Validation|null
     */
    public function getFinalisation() :?Validation
    {
        return $this->finalisation;
    }

    /**
     * @param Validation|null $finalisation
     * @return ComiteSuivi
     */
    public function setFinalisation(?Validation $finalisation) : ComiteSuivi
    {
        $this->finalisation = $finalisation;
        return $this;
    }

    /**
     * @return Validation|null
     */
    public function getValidation() : ?Validation
    {
        return $this->validation;
    }

    /**
     * @param Validation|null $validation
     * @return ComiteSuivi
     */
    public function setValidation(?Validation $validation) : ComiteSuivi
    {
        $this->validation = $validation;
        return $this;
    }


}