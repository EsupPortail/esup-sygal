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
     * @var Collection
     */
    private $document;
    /**
     * @var \Individu\Entity\Db\Individu
     */
    private $individuId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->financement = new ArrayCollection();
        $this->individu = new ArrayCollection();
        $this->inscription = new ArrayCollection();
        $this->validation = new ArrayCollection();
        $this->document = new ArrayCollection();
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
     */
    public function addFinancement(Collection $financements): Admission
    {
        foreach ($financements as $f) {
            $this->financement->add($f);
        }

        return $this;
    }

    /**
     * Remove financements.
     */
    public function removeFinancement(Collection $financements): void
    {
        foreach ($financements as $f) {
            $this->financement->removeElement($f);
        }
    }

    /**
     * Add individu.
     */
    public function addIndividu(Collection $individus): Admission
    {
        foreach ($individus as $i) {
            $this->individu->add($i);
        }

        return $this;
    }

    /**
     * Remove individus.
     */
    public function removeIndividu(Collection $individus): void
    {
        foreach ($individus as $i) {
            $this->individu->removeElement($i);
        }
    }

    /**
     * Add inscriptions.
     */
    public function addInscription(Collection $inscriptions): Admission
    {
        foreach ($inscriptions as $i) {
            $this->inscription->add($i);
        }

        return $this;
    }

    /**
     * Remove inscriptions.
     */
    public function removeInscription(Collection $inscriptions): void
    {
        foreach ($inscriptions as $i) {
            $this->inscription->removeElement($i);
        }
    }

    /**
     * Add validation.
     */
    public function addValidation(Collection $validations): self
    {
        foreach ($validations as $v) {
            $this->validation->add($v);
        }

        return $this;
    }

    /**
     * Remove validation.
     */
    public function removeValidation(Collection $validations): void
    {
        foreach ($validations as $v) {
            $this->validation->removeElement($v);
        }
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

    /**
     * Add document.
     */
    public function addDocument(Document $document)
    {
        foreach ($document as $d) {
            $this->document->add($d);
        }

        return $this;
    }

    /**
     * Remove document.
     *
     * @param Document $document
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeDocument(Document $document)
    {
        foreach ($document as $d) {
            $this->document->removeElement($d);
        }
    }

    /**
     * Get document.
     *
     * @return Collection
     */
    public function getDocument()
    {
        return $this->document;
    }
}
