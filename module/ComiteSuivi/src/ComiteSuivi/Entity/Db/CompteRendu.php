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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CompteRendu
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ComiteSuivi
     */
    public function getComite()
    {
        return $this->comite;
    }

    /**
     * @param ComiteSuivi $comite
     * @return CompteRendu
     */
    public function setComite($comite)
    {
        $this->comite = $comite;
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
     * @return CompteRendu
     */
    public function setMembre($membre)
    {
        $this->membre = $membre;
        return $this;
    }

    /**
     * @return string
     */
    public function getReponse()
    {
        return $this->reponse;
    }

    /**
     * @param string $reponse
     * @return CompteRendu
     */
    public function setReponse($reponse)
    {
        $this->reponse = $reponse;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFinaliser()
    {
        return $this->finaliser;
    }

    /**
     * @param DateTime $finaliser
     * @return CompteRendu
     */
    public function setFinaliser($finaliser)
    {
        $this->finaliser = $finaliser;
        return $this;
    }

}