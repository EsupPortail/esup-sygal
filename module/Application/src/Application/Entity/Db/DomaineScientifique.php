<?php

namespace Application\Entity\Db;


class DomaineScientifique {

    /** @var int */
    private $id;
    /** @var string */
    protected $libelle;
    /** @var \Doctrine\Common\Collections\Collection */
    protected $unites;

    public function __construct()
    {
        $this->unites = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return DomaineScientifique
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return UniteRecherche[]
     */
    public function getUnites()
    {
        return $this->unites->toArray();
    }

    /**
     * @param UniteRecherche $unite
     * @return UniteRecherche[]
     */
    public function addUnite($unite)
    {
        $this->unites[] = $unite;
        return $this->getUnites();
    }

    /**
     * @param UniteRecherche $unite
     * @return UniteRecherche[]
     */
    public function removeUnite($unite)
    {
        $this->unites->removeElement($unite);
        return $this->getUnites();
    }

}