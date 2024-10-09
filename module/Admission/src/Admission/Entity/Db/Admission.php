<?php
namespace Admission\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Notification\Exception\RuntimeException;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenAvis\Entity\Db\AvisType;

class Admission implements HistoriqueAwareInterface, ResourceInterface{

    use HistoriqueAwareTrait;

    const ETAT_EN_COURS_SAISIE   = 'C';
    const ETAT_EN_COURS_VALIDATION   = 'E';
    const ETAT_ABANDONNE = 'A';
    const ETAT_VALIDE = 'V';
    const ETAT_REJETE = 'R';

    public static $etatsLibelles = [
        self::ETAT_EN_COURS_SAISIE => "En cours de saisie",
        self::ETAT_EN_COURS_VALIDATION => "En cours de validation",
        self::ETAT_ABANDONNE => "Abandonné",
        self::ETAT_VALIDE => "Validé",
        self::ETAT_REJETE => "Rejeté",
    ];

    private ?Etat $etat = null;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $numeroCandidat;

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
    /** @var Collection|AdmissionAvis[] */
    private $admissionAvis;
    /**
     * @var Collection
     */
    private $conventionFormationDoctorale;

    /**
     * @var Collection
     */
    private $document;

    /**
     * @var Individu
     */
    private $individu;

    /** @var AdmissionAvis | AdmissionValidation|null */
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
        $this->admissionAvis = new ArrayCollection();
        $this->conventionFormationDoctorale = new ArrayCollection();
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
     * Set numeroCandidat.
     *
     * @param string|null $numeroCandidat
     *
     * @return Admission
     */
    public function setNumeroCandidat($numeroCandidat = null)
    {
        $this->numeroCandidat = $numeroCandidat;

        return $this;
    }

    /**
     * Get numeroCandidat.
     *
     * @return string|null
     */
    public function getNumeroCandidat()
    {
        return $this->numeroCandidat;
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
     * @return AdmissionAvis[]|Collection
     */
    public function getAdmissionAvis(bool $includeHistorises = false): Collection
    {
        if ($includeHistorises) {
            return $this->admissionAvis;
        }

        return $this->admissionAvis->filter(function(AdmissionAvis $admissionAvis) {
            return $admissionAvis->estNonHistorise();
        });
    }

    /**
     * Retourne l'éventuel avis sur ce dossier d'admission, du type spécifié.
     */
    public function getAdmissionAvisOfType(AvisType $avisType): ?AdmissionAvis
    {
        $aviss = $this->getAdmissionAvis()->filter(function(AdmissionAvis $avis) use ($avisType) {
            return $avis->getAvis()->getAvisType() === $avisType;
        });

        if (count($aviss) > 1) {
            throw new RuntimeException("Anomalie : plusieurs avis de dossier d'admission du même type trouvées");
        }

        return $aviss->first() ?: null;
    }

    public function addAdmissionAvis(AdmissionAvis $admissionAvis): self
    {
        $this->admissionAvis->add($admissionAvis);

        return $this;
    }

    public function removeAdmissionAvis(AdmissionAvis $admissionAvis): self
    {
        $this->admissionAvis->removeElement($admissionAvis);

        return $this;
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
        $this->operationPossible?->setAdmission($this);

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

    public function isDossierComplet(){
        /** @var Verification $verificationEtudiant */
        $verificationEtudiant = $this->getEtudiant()->first()->getVerificationEtudiant()->first()
            ? $this->getEtudiant()->first()->getVerificationEtudiant()->first()
            : null;
        $isCompletEtudiant = $verificationEtudiant && $verificationEtudiant->getEstComplet();

        /** @var Verification $verificationInscription */
        $verificationInscription = $this->getInscription()->first() && $this->getInscription()->first()->getVerificationInscription()->first()
        ? $this->getInscription()->first()->getVerificationInscription()->first()
        : null;
        $isCompletInscription = $verificationInscription && $verificationInscription->getEstComplet();

        /** @var Verification $verificationFinancement */
        $verificationFinancement = $this->getFinancement()->first() && $this->getFinancement()->first()->getVerificationFinancement()->first()
            ? $this->getFinancement()->first()->getVerificationFinancement()->first()
        : null;
        $isCompletFinancement = $verificationFinancement && $verificationFinancement->getEstComplet();

        /** @var Verification $verificationDocument */
        $verificationDocument = $this->getDocument()->first() && $this->getDocument()->first()->getVerificationDocument()->first()
        ? $this->getDocument()->first()->getVerificationDocument()->first()
        : null;
        $isCompletDocument = $verificationDocument && $verificationDocument->getEstComplet();

        return $isCompletEtudiant && $isCompletInscription && $isCompletFinancement && $isCompletDocument;
    }

    public function hasComments(){
        /** @var Verification $verificationEtudiant */
        $verificationEtudiant = $this->getEtudiant()->first() && $this->getEtudiant()->first()->getVerificationEtudiant()->first()
            ? $this->getEtudiant()->first()->getVerificationEtudiant()->first()
            : null;
        $hasCommentairesEtudiant = $verificationEtudiant && $verificationEtudiant->getCommentaire();

        /** @var Verification $verificationInscription */
        $verificationInscription = $this->getInscription()->first() && $this->getInscription()->first()->getVerificationInscription()->first()
            ? $this->getInscription()->first()->getVerificationInscription()->first()
            : null;
        $hasCommentairesInscription = $verificationInscription && $verificationInscription->getCommentaire();

        /** @var Verification $verificationFinancement */
        $verificationFinancement = $this->getFinancement()->first() && $this->getFinancement()->first()->getVerificationFinancement()->first()
            ? $this->getFinancement()->first()->getVerificationFinancement()->first()
            : null;
        $hasCommentairesFinancement = $verificationFinancement && $verificationFinancement->getCommentaire();

        /** @var Verification $verificationDocument */
        $verificationDocument = $this->getDocument()->first() && $this->getDocument()->first()->getVerificationDocument()->first()
            ? $this->getDocument()->first()->getVerificationDocument()->first()
            : null;
        $hasCommentairesDocument = $verificationDocument && $verificationDocument->getCommentaire();

        return $hasCommentairesEtudiant || $hasCommentairesInscription || $hasCommentairesFinancement || $hasCommentairesDocument;
    }

    /**
     * Add conventionFormationDoctorale.
     *
     * @param ConventionFormationDoctorale $conventionFormationDoctorale
     *
     * @return Admission
     */
    public function addConventionFormationDoctorale(ConventionFormationDoctorale $conventionFormationDoctorale)
    {
        $this->conventionFormationDoctorale[] = $conventionFormationDoctorale;

        return $this;
    }

    /**
     * Remove conventionFormationDoctorale.
     *
     * @param ConventionFormationDoctorale $conventionFormationDoctorale
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeConventionFormationDoctorale(ConventionFormationDoctorale $conventionFormationDoctorale)
    {
        return $this->conventionFormationDoctorale->removeElement($conventionFormationDoctorale);
    }

    /**
     * Get conventionFormationDoctorale.
     *
     * @return Collection
     */
    public function getConventionFormationDoctorale()
    {
        return $this->conventionFormationDoctorale;
    }
}
