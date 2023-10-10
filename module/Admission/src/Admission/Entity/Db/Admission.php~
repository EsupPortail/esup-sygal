<?php
namespace Admission\Entity\Db;

use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Admission implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var int|null
     */
    private $etatId;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Collection
     */
    private $financements;

    /**
     * @var Collection
     */
    private $individus;

    /**
     * @var Collection
     */
    private $inscriptions;

    /**
     * @var Collection
     */
    private $validations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->financements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->individus = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->validations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set etatId.
     *
     * @param int|null $etatId
     *
     * @return Admission
     */
    public function setEtatId($etatId = null)
    {
        $this->etatId = $etatId;

        return $this;
    }

    /**
     * Get etatId.
     *
     * @return int|null
     */
    public function getEtatId()
    {
        return $this->etatId;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add financement.
     *
     * @param Financement $financement
     *
     * @return Admission
     */
    public function addFinancement(Financement $financement)
    {
        $this->financements[] = $financement;

        return $this;
    }

    /**
     * Remove financement.
     *
     * @param Financement $financement
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFinancement(Financement $financement)
    {
        return $this->financements->removeElement($financement);
    }

    /**
     * Get financements.
     *
     * @return Collection
     */
    public function getFinancements()
    {
        return $this->financements;
    }

    /**
     * Add individu.
     *
     * @param Individu $individu
     *
     * @return Admission
     */
    public function addIndividu(Individu $individu)
    {
        $this->individus[] = $individu;

        return $this;
    }

    /**
     * Remove individu.
     *
     * @param Individu $individu
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeIndividu(Individu $individu)
    {
        return $this->individus->removeElement($individu);
    }

    /**
     * Get individus.
     *
     * @return Collection
     */
    public function getIndividus()
    {
        return $this->individus;
    }

    /**
     * Add inscription.
     *
     * @param Inscription $inscription
     *
     * @return Admission
     */
    public function addInscription(Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param Inscription $inscription
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscription(Inscription $inscription)
    {
        return $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions.
     *
     * @return Collection
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * Add validation.
     *
     * @param Validation $validation
     *
     * @return Admission
     */
    public function addValidation(Validation $validation)
    {
        $this->validations[] = $validation;

        return $this;
    }

    /**
     * Remove validation.
     *
     * @param Validation $validation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeValidation(Validation $validation)
    {
        return $this->validations->removeElement($validation);
    }

    /**
     * Get validations.
     *
     * @return Collection
     */
    public function getValidations()
    {
        return $this->validations;
    }
}
