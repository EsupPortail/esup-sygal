<?php

namespace Soutenance\Entity;

use Depot\Entity\Db\FichierThese;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Justificatif implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var integer */
    private $id;
    /** @var Proposition */
    private $proposition;
    /** @var FichierThese */
    private $fichier;
    /** @var Membre */
    private $membre;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return Justificatif
     */
    public function setProposition($proposition)
    {
        $this->proposition = $proposition;
        return $this;
    }

    /**
     * @return FichierThese
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * @param FichierThese $fichier
     * @return Justificatif
     */
    public function setFichier($fichier)
    {
        $this->fichier = $fichier;
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
     * @return Justificatif
     */
    public function setMembre($membre)
    {
        $this->membre = $membre;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMembre()
    {
        return ($this->membre !== null);
    }
}