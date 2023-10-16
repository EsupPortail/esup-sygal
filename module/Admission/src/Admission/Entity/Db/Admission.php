<?php
namespace Admission\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
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
    private ?int $id = null;


    /**
     * @var Collection
     */
    private $financement;

    /**
     * @var Collection
     */
    private $individu;

    /**
     * @var Collection
     */
    private $inscription;

    /**
     * @var Collection
     */
    private $validation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->financement = new ArrayCollection();
        $this->individu = new ArrayCollection();
        $this->inscription = new ArrayCollection();
        $this->validation = new ArrayCollection();
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
    public function addFinancement(Financement $financement): Admission
    {
        $this->financement[] = $financement;

        return $this;
    }

    /**
     * Remove financement.
     *
     * @param Financement $financement
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFinancement(Financement $financement): bool
    {
        return $this->financement->removeElement($financement);
    }

    /**
     * Add individu.
     *
     * @param Individu $individu
     *
     * @return Admission
     */
    public function addIndividu(Individu $individu): Admission
    {
        $this->individu[] = $individu;

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
        return $this->individu->removeElement($individu);
    }

    /**
     * Add inscription.
     *
     * @param Inscription $inscription
     *
     * @return Admission
     */
    public function addInscription(Inscription $inscription): Admission
    {
        $this->inscription[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param Inscription $inscription
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscription(Inscription $inscription): bool
    {
        return $this->inscription->removeElement($inscription);
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
        $this->validation[] = $validation;

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
        return $this->validation->removeElement($validation);
    }

    /**
     * Get financement.
     *
     * @return Collection
     */
    public function getFinancement()
    {
        return $this->financement;
    }

    /**
     * Get individu.
     *
     * @return Collection
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * Get inscription.
     *
     * @return Collection
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * Get validation.
     *
     * @return Collection
     */
    public function getValidation()
    {
        return $this->validation;
    }
    /**
     * @var \Individu\Entity\Db\Individu
     */
    private $individuId;


    /**
     * Set individuId.
     *
     * @param \Individu\Entity\Db\Individu|null $individuId
     *
     * @return Admission
     */
    public function setIndividuId(\Individu\Entity\Db\Individu $individuId = null)
    {
        $this->individuId = $individuId;

        return $this;
    }

    /**
     * Get individuId.
     *
     * @return \Individu\Entity\Db\Individu|null
     */
    public function getIndividuId()
    {
        return $this->individuId;
    }
}
