<?php

namespace Soutenance\Entity;

class Qualite
{
    const ID_INCONNUE = 0;

    /** @var int */
    private $id;
    /** @var string */
    private $libelle;
    /** @var string */
    private $rang;
    /** @var string */
    private $hdr;

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

    /**
     * @return string
     */
    public function getHdr()
    {
        return $this->hdr;
    }

    /**
     * @param string $hdr
     * @return Qualite
     */
    public function setHdr($hdr)
    {
        $this->hdr = $hdr;
        return $this;
    }

    public function __toString()
    {
        return $this->getLibelle();
    }


}

