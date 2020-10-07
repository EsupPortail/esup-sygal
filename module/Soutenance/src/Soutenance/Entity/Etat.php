<?php

namespace Soutenance\Entity;

use UnicaenApp\Entity\HistoriqueAwareTrait;

class Etat {
    use HistoriqueAwareTrait;

    const EN_COURS = 'EN_COURS';
    const VALIDEE = 'VALIDEE';
    const REJETEE = 'REJETEE';

    /** @var integer */
    private $id;
    /** @var string */
    private $code;
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Etat
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return Etat
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

}