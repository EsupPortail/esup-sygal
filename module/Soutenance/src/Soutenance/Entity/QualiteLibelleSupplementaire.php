<?php

namespace Soutenance\Entity;

use UnicaenApp\Entity\HistoriqueAwareTrait;

class QualiteLibelleSupplementaire {
    use HistoriqueAwareTrait;

    /** @var integer */
    private $id;
    /** @var Qualite */
    private $qualite;
    /** @var string */
    private $libelle;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Qualite
     */
    public function getQualite()
    {
        return $this->qualite;
    }

    /**
     * @param Qualite $qualite
     * @return QualiteLibelleSupplementaire
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return QualiteLibelleSupplementaire
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }
}