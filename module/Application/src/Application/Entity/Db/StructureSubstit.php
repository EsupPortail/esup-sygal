<?php

namespace Application\Entity\Db;

/**
 * StructureSubstit
 */
class StructureSubstit
{
    /**
     * @var \DateTime
     */
    private $histoCreation;

    /**
     * @var \DateTime
     */
    private $histoDestruction;

    /**
     * @var \DateTime
     */
    private $histoModification;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var Structure
     */
    private $fromStructure;

    /**
     * @var Structure
     */
    private $toStructure;

    /**
     * @var Utilisateur
     */
    private $histoModificateur;

    /**
     * @var Utilisateur
     */
    private $histoDestructeur;

    /**
     * @var Utilisateur
     */
    private $histoCreateur;


    /**
     * Set histoCreation
     *
     * @param \DateTime $histoCreation
     *
     * @return StructureSubstit
     */
    public function setHistoCreation($histoCreation)
    {
        $this->histoCreation = $histoCreation;

        return $this;
    }

    /**
     * Get histoCreation
     *
     * @return \DateTime
     */
    public function getHistoCreation()
    {
        return $this->histoCreation;
    }

    /**
     * Set histoDestruction
     *
     * @param \DateTime $histoDestruction
     *
     * @return StructureSubstit
     */
    public function setHistoDestruction($histoDestruction)
    {
        $this->histoDestruction = $histoDestruction;

        return $this;
    }

    /**
     * Get histoDestruction
     *
     * @return \DateTime
     */
    public function getHistoDestruction()
    {
        return $this->histoDestruction;
    }

    /**
     * Set histoModification
     *
     * @param \DateTime $histoModification
     *
     * @return StructureSubstit
     */
    public function setHistoModification($histoModification)
    {
        $this->histoModification = $histoModification;

        return $this;
    }

    /**
     * Get histoModification
     *
     * @return \DateTime
     */
    public function getHistoModification()
    {
        return $this->histoModification;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fromStructure
     *
     * @param Structure $fromStructure
     *
     * @return StructureSubstit
     */
    public function setFromStructure(Structure $fromStructure = null)
    {
        $this->fromStructure = $fromStructure;

        return $this;
    }

    /**
     * Get fromStructure
     *
     * @return Structure
     */
    public function getFromStructure()
    {
        return $this->fromStructure;
    }

    /**
     * Set toStructure
     *
     * @param Structure $toStructure
     *
     * @return StructureSubstit
     */
    public function setToStructure(Structure $toStructure = null)
    {
        $this->toStructure = $toStructure;

        return $this;
    }

    /**
     * Get toStructure
     *
     * @return Structure
     */
    public function getToStructure()
    {
        return $this->toStructure;
    }

    /**
     * Set histoModificateur
     *
     * @param Utilisateur $histoModificateur
     *
     * @return StructureSubstit
     */
    public function setHistoModificateur(Utilisateur $histoModificateur = null)
    {
        $this->histoModificateur = $histoModificateur;

        return $this;
    }

    /**
     * Get histoModificateur
     *
     * @return Utilisateur
     */
    public function getHistoModificateur()
    {
        return $this->histoModificateur;
    }

    /**
     * Set histoDestructeur
     *
     * @param Utilisateur $histoDestructeur
     *
     * @return StructureSubstit
     */
    public function setHistoDestructeur(Utilisateur $histoDestructeur = null)
    {
        $this->histoDestructeur = $histoDestructeur;

        return $this;
    }

    /**
     * Get histoDestructeur
     *
     * @return Utilisateur
     */
    public function getHistoDestructeur()
    {
        return $this->histoDestructeur;
    }

    /**
     * Set histoCreateur
     *
     * @param Utilisateur $histoCreateur
     *
     * @return StructureSubstit
     */
    public function setHistoCreateur(Utilisateur $histoCreateur = null)
    {
        $this->histoCreateur = $histoCreateur;

        return $this;
    }

    /**
     * Get histoCreateur
     *
     * @return Utilisateur
     */
    public function getHistoCreateur()
    {
        return $this->histoCreateur;
    }
}

