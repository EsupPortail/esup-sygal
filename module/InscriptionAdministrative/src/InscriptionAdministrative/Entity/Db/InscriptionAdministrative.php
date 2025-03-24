<?php

namespace InscriptionAdministrative\Entity\Db;

use Application\Constants;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Structure\Entity\Db\EcoleDoctorale;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/********************************************************************************************************************
 * Inscriptions administratives en vrac reçues de Pégase.
 ********************************************************************************************************************
 *
 * Données *non remaniées* issues de la synchro spécifiée dans la classe
 * {@see \SygalApiImpl\V1\Rest\InscriptionAdministrative\Process\InscriptionAdministrativeImportProcess}
 *
 * NB : l'id de l'inscription administrative est forgé (côté Pégase) à partir des attributs suivants :
 * - la structure organisationnelle (code UAI, ex : 'ETAB00'),
 * - l'apprenant (code apprenant, ex : '000001059'),
 * - le chemin formation (ex : 'M-BIO-SANT>M1-BIO-SANT'),
 * - le code période (ex : 'PER-2023').
 *
 * Ex d'id reçu : "ETAB00@000001059@M-BIO-SANT>M1-BIO-SANT@PER-2023"
 *
 * Cela pose problème : si l'inscription est modifiée dans Pégase (i.e. la valeur de l'un des attributs change),
 * SyGAL crée une nouvelle inscription !
 * On l'a signalé à PC-Scol mais aucune amélioration prévue de leur côté.
 * On a donc décidé de créer {@see \InscriptionAdministrative\Entity\Db\InscriptionAdministrative}
 *
 */
class InscriptionAdministrative implements HistoriqueAwareInterface
{
    const STATUT_INSCRIPTION_VALIDE = 'VALIDE';
    const STATUT_INSCRIPTION_ANNULE = 'ANNULEE';
    const STATUTS_INSCRIPTION_VALIDES = [
        self::STATUT_INSCRIPTION_VALIDE,
        self::STATUT_INSCRIPTION_ANNULE,
    ];

    const COMMENTAIRE_ANNEE_UNIV_PERIODE =
        "Déduite de la date de début de période et du paramétrage définissant la date de début et de fin d'une année universitaire";

    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    private int $id;
    private string $sourceCode;
    private string $noCandidat;
    private DateTime $dateInscription;
    private ?DateTime $dateAnnulation = null;
    private string $cesure;
    private string $chemin;
    private string $codeStructureEtablissementDuChemin;
    private ?string $formation = null;
    private string $mobilite;
    private string $origine;
    private bool $principale;
    private ?string $regimeInscriptionLibelle;
    private string $statutInscription;
    private string $periodeCode;
    private string $periodeLibelle;
    private DateTime $periodeDateDebut;
    private DateTime $periodeDateFin;
    private ?int $periodeAnneeUniversitaire = null;

    private EcoleDoctorale $ecoleDoctorale;
    private Collection $inscriptionAdministrativeApprenant;
    private Collection $inscriptionAdministrativeDoctorant;

    public function __construct()
    {
        $this->inscriptionAdministrativeApprenant = new ArrayCollection();
        $this->inscriptionAdministrativeDoctorant = new ArrayCollection();
    }

    public function estValidee(): bool
    {
        return $this->getStatutInscription() === static::STATUT_INSCRIPTION_VALIDE;
    }

    public function estAnnulee(): bool
    {
        return $this->getStatutInscription() === static::STATUT_INSCRIPTION_ANNULE;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     */
    public function getNoCandidat(): string
    {
        return $this->noCandidat;
    }

    /**
     * @return \DateTime
     */
    public function getDateInscription(): DateTime
    {
        return $this->dateInscription;
    }

    /**
     * @return string
     */
    public function getDateInscriptionToString(): string
    {
        return $this->dateInscription->format(Constants::DATE_FORMAT);
    }

    /**
     * @return \DateTime|null
     */
    public function getDateAnnulation(): ?DateTime
    {
        return $this->dateAnnulation;
    }

    /**
     * @return string
     */
    public function getDateAnnulationToString(): string
    {
        return $this->dateAnnulation?->format(Constants::DATE_FORMAT) ?: '';
    }

    /**
     * @return string
     */
    public function getCesure(): string
    {
        return $this->cesure;
    }

    /**
     * @return string
     */
    public function getChemin(): string
    {
        return $this->chemin;
    }

    /**
     * @return string
     */
    public function getCodeStructureEtablissementDuChemin(): string
    {
        return $this->codeStructureEtablissementDuChemin;
    }

    /**
     * @return string|null
     */
    public function getFormation(): ?string
    {
        return $this->formation;
    }

    /**
     * @return string
     */
    public function getMobilite(): string
    {
        return $this->mobilite;
    }

    /**
     * @return string
     */
    public function getOrigine(): string
    {
        return $this->origine;
    }

    /**
     * @return bool
     */
    public function isPrincipale(): bool
    {
        return $this->principale;
    }

    /**
     * @return string|null
     */
    public function getRegimeInscriptionLibelle(): ?string
    {
        return $this->regimeInscriptionLibelle;
    }

    /**
     * @return string
     */
    public function getStatutInscription(): string
    {
        return $this->statutInscription;
    }

    /**
     * @return string
     */
    public function getPeriodeCode(): string
    {
        return $this->periodeCode;
    }

    /**
     * @return string
     */
    public function getPeriodeLibelle(): string
    {
        return $this->periodeLibelle;
    }

    /**
     * @return \DateTime
     */
    public function getPeriodeDateDebut(): DateTime
    {
        return $this->periodeDateDebut;
    }

    /**
     * @return string
     */
    public function getPeriodeDateDebutToString(): string
    {
        return $this->periodeDateDebut->format(Constants::DATE_FORMAT);
    }

    /**
     * @return \DateTime
     */
    public function getPeriodeDateFin(): DateTime
    {
        return $this->periodeDateFin;
    }

    /**
     * @return string
     */
    public function getPeriodeDateFinToString(): string
    {
        return $this->periodeDateFin->format(Constants::DATE_FORMAT);
    }

    /**
     * @return int|null
     */
    public function getPeriodeAnneeUniversitaire(): ?int
    {
        return $this->periodeAnneeUniversitaire;
    }

//    public function getAdmission(): Admission
//    {
//        return $this->admission;
//    }

    /**
     * @return \Structure\Entity\Db\EcoleDoctorale
     */
    public function getEcoleDoctorale(): EcoleDoctorale
    {
        return $this->ecoleDoctorale;
    }

    public function getInscriptionAdministrativeApprenant(): ?InscriptionAdministrativeApprenant
    {
        return $this->inscriptionAdministrativeApprenant->first() ?: null;
    }

    public function getInscriptionAdministrativeDoctorant(): ?InscriptionAdministrativeDoctorant
    {
        return $this->inscriptionAdministrativeDoctorant->first() ?: null;
    }

    /**
     * Ajoute à cette IA un lien vers un Doctorant.
     */
    public function addInscriptionAdministrativeDoctorantForDoctorant(Doctorant $doctorant): InscriptionAdministrativeDoctorant
    {
        $iad = (new InscriptionAdministrativeDoctorant())
            ->setDoctorant($doctorant)
            ->setInscriptionAdministrative($this);

        $this->inscriptionAdministrativeDoctorant->add($iad);

        return $iad;
    }
}