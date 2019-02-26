<?php

namespace Soutenance\Entity;

class Qualite
{

    /** @var int */
    private $id;
    /** @var string */
    private $libelle;
    /** @var string */
    private $rang;

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
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return Qualite
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * @param string $rang
     * @return Qualite
     */
    public function setRang($rang)
    {
        $this->rang = $rang;
        return $this;
    }

    public function __toString()
    {
        return $this->getLibelle();
    }


}

