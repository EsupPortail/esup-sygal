<?php

namespace ComiteSuivi\Entity\Db;

use Application\Entity\Db\Fichier;
use DateTime;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class CompteRendu {
    use HistoriqueAwareTrait;

    /** @var integer */
    private $id;
    /** @var ComiteSuivi */
    private $comite;
    /** @var Membre */
    private $membre;
    /** @var Fichier */
    private $fichier;
    /** @var DateTime */
    private $finaliser;

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CompteRendu
     */
    public function setId(int $id) : CompteRendu
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ComiteSuivi|null
     */
    public function getComite() : ?ComiteSuivi
    {
        return $this->comite;
    }

    /**
     * @param ComiteSuivi|null $comite
     * @return CompteRendu
     */
    public function setComite(?ComiteSuivi $comite) : CompteRendu
    {
        $this->comite = $comite;
        return $this;
    }

    /**
     * @return Membre|null
     */
    public function getMembre() : ?Membre
    {
        return $this->membre;
    }

    /**
     * @param Membre|null $membre
     * @return CompteRendu
     */
    public function setMembre(?Membre $membre) : CompteRendu
    {
        $this->membre = $membre;
        return $this;
    }

    /**
     * @return Fichier|null
     */
    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    /**
     * @param Fichier|null $fichier
     * @return CompteRendu
     */
    public function setFichier(?Fichier $fichier): CompteRendu
    {
        $this->fichier = $fichier;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getFinaliser() : ?DateTime
    {
        return $this->finaliser;
    }

    /**
     * @param DateTime|null $finaliser
     * @return CompteRendu
     */
    public function setFinaliser(?DateTime $finaliser) : CompteRendu
    {
        $this->finaliser = $finaliser;
        return $this;
    }

}