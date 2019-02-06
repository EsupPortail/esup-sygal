<?php

namespace Information\Entity\Db;

use Application\Entity\Db\Utilisateur;
use DateTime;

class InformationFichier {
    /** @var integer */
    private $id;
    /** @var string */
    private $nom;
    /** @var string */
    private $filename;
    /** @var Utilisateur */
    private $createur;
    /** @var DateTime */
    private $dateCreation;

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
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return InformationFichier
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return Utilisateur
     */
    public function getCreateur()
    {
        return $this->createur;
    }

    /**
     * @param Utilisateur $createur
     * @return InformationFichier
     */
    public function setCreateur($createur)
    {
        $this->createur = $createur;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param DateTime $dateCreation
     * @return InformationFichier
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return InformationFichier
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }



}