<?php

namespace ComiteSuivi\Entity\Db;

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


}