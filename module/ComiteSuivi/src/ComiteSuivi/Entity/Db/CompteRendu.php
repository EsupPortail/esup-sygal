<?php

namespace ComiteSuivi\Entity\Db;

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
    /** @var string */
    private $reponse;
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
     * @return string|null
     */
    public function getReponse() : ?string
    {
        return $this->reponse;
    }

    /**
     * @param string|null $reponse
     * @return CompteRendu
     */
    public function setReponse(?string $reponse) : CompteRendu
    {
        $this->reponse = $reponse;
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