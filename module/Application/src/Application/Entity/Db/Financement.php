<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;

class Financement {

    /** @var int */
    private $id;
    /** @var string */
    private $code;
    /** @var string */
    private $libelle;
    /** @var ArrayCollection */
    private $theses;

    public function __construct()
    {
        $this->theses = new ArrayCollection();
    }

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
     * @return Financement
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
     * @return Financement
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTheses()
    {
        return $this->theses;
    }

    /**
     * @param These $these
     * @return Financement
     */
    public function addThese($these)
    {
        $this->theses[] = $these;
        return $this;
    }

    /**
     * @param These $these
     * @return Financement
     */
    public function removeThese($these)
    {
        $this->theses->removeElement($these);
        return $this;
    }

}