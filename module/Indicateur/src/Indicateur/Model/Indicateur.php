<?php

namespace Indicateur\Model;

class Indicateur {

    const THESE = 'THESE';
    const INDIVIDU = 'INDIVIDU';
    const STRUCTURE = 'STRUCTURE';

    /** @var int */
    private $id;
    /** @var string */
    private $libelle;
    /** @var string */
    private $description;
    /** @var string */
    private $requete;
    /** @var boolean */
    private $actif;
    /** @var string */
    private $displayAs;
    /** @var string */
    private $class;

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
     * @return Indicateur
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Indicateur
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequete()
    {
        return $this->requete;
    }

    /**
     * @param string $requete
     * @return Indicateur
     */
    public function setRequete($requete)
    {
        $this->requete = $requete;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActif()
    {
        return $this->actif;
    }

    /**
     * @param bool $actif
     * @return Indicateur
     */
    public function setActif($actif)
    {
        $this->actif = $actif;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayAs()
    {
        return $this->displayAs;
    }

    /**
     * @param string $displayAs
     * @return Indicateur
     */
    public function setDisplayAs($displayAs)
    {
        $this->displayAs = $displayAs;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return Indicateur
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }



}