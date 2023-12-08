<?php
namespace Admission\Entity\Db;

use Admission\Service\Operation\AdmissionOperationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Notification\Exception\RuntimeException;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Admission implements HistoriqueAwareInterface, ResourceInterface{

    use HistoriqueAwareTrait;

    const ETAT_EN_COURS   = 'C';
    const ETAT_ABANDONNE = 'A';
    const ETAT_VALIDE = 'V';

    const ETATS = [
        self::ETAT_EN_COURS => self::ETAT_EN_COURS,
        self::ETAT_ABANDONNE => self::ETAT_ABANDONNE,
        self::ETAT_VALIDE => self::ETAT_VALIDE,
    ];

    private ?Etat $etat = null;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Collection
     */
    private $financement;

    /**
     * @var Collection
     */
    private $etudiant;

    /**
     * @var Collection
     */
    private $inscription;

    /** @var Collection|AdmissionValidation[] */
    private $admissionValidations;

    /**
     * @var Collection
     */
    private $document;

    /**
     * @var Individu
     */
    private $individu;

    /** @var AdmissionValidation|null */
    private $operationPossible = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->financement = new ArrayCollection();
        $this->etudiant = new ArrayCollection();
        $this->inscription = new ArrayCollection();
        $this->admissionValidations = new ArrayCollection();
        $this->document = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "dossier d'admission";
    }


    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): void
    {
        $this->etat = $etat;
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
     * Get financement.
     *
     * @return Collection
     */
    public function getFinancement()
    {
        return $this->financement;
    }

    /**
     * Add etudiant.
     */
    public function addEtudiant(Collection $etudiants)
    {
        foreach ($etudiants as $e) {
            $this->etudiant->add($e);
        }

        return $this;
    }

    /**
     * Remove etudiant.
     */
    public function removeEtudiant(Collection $etudiants)
    {
        foreach ($etudiants as $e) {
            $this->etudiant->removeElement($e);
        }
    }

    /**
     * Get etudiant.
     *
     * @return Collection
     */
    public function getEtudiant()
    {
        return $this->etudiant;
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
     * Get inscription.
     *
     * @return Collection
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * Add validation.
     */
    public function getAdmissionValidationOfType(TypeValidation $typeValidation): ?AdmissionValidation
    {
        $admissionValidations = $this->getAdmissionValidations()->filter(function(AdmissionValidation $v) use ($typeValidation) {
            return $v->getTypeValidation() === $typeValidation;
        });

        if (count($admissionValidations) > 1) {
            throw new \RuntimeException("Anomalie : plusieurs admissionValidations du même type trouvées");
        }

        return $admissionValidations->first() ?: null;
    }

    /**
     * @param bool $includeHistorises
     * @return Collection
     */
    public function getAdmissionValidations(bool $includeHistorises = false): Collection
    {
        if ($includeHistorises) {
            return $this->admissionValidations;
        }

        return $this->admissionValidations->filter(function(AdmissionValidation $v) {
            return $v->estNonHistorise();
        });
    }

    public function addAdmissionValidation(AdmissionValidation $validation): self
    {
        $this->admissionValidations->add($validation);

        return $this;
    }

    public function removeAdmissionValidation(AdmissionValidation $validation): self
    {
        $this->admissionValidations->removeElement($validation);

        return $this;
    }

    /**
     * Add document.
     */
    public function addDocument(Collection $documents)
    {
        foreach ($documents as $d) {
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
    public function removeDocument(Collection $documents)
    {
        foreach ($documents as $d) {
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

    /**
     * Set individu.
     *
     * @param Individu|null $individu
     *
     * @return Admission
     */
    public function setIndividu(Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Get individu.
     *
     * @return Individu|null
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * Injecte l'opération éventuelle qu'il est possible de réaliser sur ce dossier d'admission.
     */
    public function setOperationPossible(?AdmissionOperationInterface $operation = null): self
    {
        $this->operationPossible = $operation;
        if ($this->operationPossible) {
            $this->operationPossible->setAdmission($this);
        }

        return $this;
    }

    public function getOperationPossible()
    {
        return $this->operationPossible;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getDateToString() : string
    {
        $date = $this->getHistoCreation()->format('d/m/Y à H:i');
        return $date;
    }

    public function getResourceId()
    {
        return "Admission";
    }
}
