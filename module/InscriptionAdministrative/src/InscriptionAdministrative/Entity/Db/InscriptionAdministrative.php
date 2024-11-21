<?php

namespace InscriptionAdministrative\Entity\Db;

use Application\Constants;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Structure\Entity\Db\EcoleDoctorale;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class InscriptionAdministrative implements HistoriqueAwareInterface, ResourceInterface
{
    const STATUT_INSCRIPTION_VALIDE = 'VALIDE';
    const STATUT_INSCRIPTION_ANNULE = 'ANNULEE';

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

    private Doctorant $doctorant;
    private EcoleDoctorale $ecoleDoctorale;

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
     * @param string $sourceCode
     * @return self
     */
    public function setSourceCode(string $sourceCode): self
    {
        $this->sourceCode = $sourceCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getNoCandidat(): string
    {
        return $this->noCandidat;
    }

    /**
     * @param string $noCandidat
     * @return self
     */
    public function setNoCandidat(string $noCandidat): self
    {
        $this->noCandidat = $noCandidat;
        return $this;
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
     * @param \DateTime $dateInscription
     * @return self
     */
    public function setDateInscription(DateTime $dateInscription): self
    {
        $this->dateInscription = $dateInscription;
        return $this;
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
     * @param \DateTime|null $dateAnnulation
     * @return self
     */
    public function setDateAnnulation(?DateTime $dateAnnulation): self
    {
        $this->dateAnnulation = $dateAnnulation;
        return $this;
    }

    /**
     * @return string
     */
    public function getCesure(): string
    {
        return $this->cesure;
    }

    /**
     * @param string $cesure
     * @return self
     */
    public function setCesure(string $cesure): self
    {
        $this->cesure = $cesure;
        return $this;
    }

    /**
     * @return string
     */
    public function getChemin(): string
    {
        return $this->chemin;
    }

    /**
     * @param string $chemin
     * @return self
     */
    public function setChemin(string $chemin): self
    {
        $this->chemin = $chemin;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeStructureEtablissementDuChemin(): string
    {
        return $this->codeStructureEtablissementDuChemin;
    }

    /**
     * @param string $codeStructureEtablissementDuChemin
     * @return self
     */
    public function setCodeStructureEtablissementDuChemin(string $codeStructureEtablissementDuChemin): self
    {
        $this->codeStructureEtablissementDuChemin = $codeStructureEtablissementDuChemin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormation(): ?string
    {
        return $this->formation;
    }

    /**
     * @param string $formation
     * @return self
     */
    public function setFormation(string $formation): self
    {
        $this->formation = $formation;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobilite(): string
    {
        return $this->mobilite;
    }

    /**
     * @param string $mobilite
     * @return self
     */
    public function setMobilite(string $mobilite): self
    {
        $this->mobilite = $mobilite;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigine(): string
    {
        return $this->origine;
    }

    /**
     * @param string $origine
     * @return self
     */
    public function setOrigine(string $origine): self
    {
        $this->origine = $origine;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPrincipale(): bool
    {
        return $this->principale;
    }

    /**
     * @param bool $principale
     * @return self
     */
    public function setPrincipale(bool $principale): self
    {
        $this->principale = $principale;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegimeInscriptionLibelle(): ?string
    {
        return $this->regimeInscriptionLibelle;
    }

    /**
     * @param string|null $regimeInscriptionLibelle
     * @return self
     */
    public function setRegimeInscriptionLibelle(?string $regimeInscriptionLibelle): self
    {
        $this->regimeInscriptionLibelle = $regimeInscriptionLibelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatutInscription(): string
    {
        return $this->statutInscription;
    }

    /**
     * @param string $statutInscription
     * @return self
     */
    public function setStatutInscription(string $statutInscription): self
    {
        $this->statutInscription = $statutInscription;
        return $this;
    }

    /**
     * @return string
     */
    public function getPeriodeCode(): string
    {
        return $this->periodeCode;
    }

    /**
     * @param string $periodeCode
     * @return self
     */
    public function setPeriodeCode(string $periodeCode): self
    {
        $this->periodeCode = $periodeCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPeriodeLibelle(): string
    {
        return $this->periodeLibelle;
    }

    /**
     * @param string $periodeLibelle
     * @return self
     */
    public function setPeriodeLibelle(string $periodeLibelle): self
    {
        $this->periodeLibelle = $periodeLibelle;
        return $this;
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
     * @param \DateTime $periodeDateDebut
     * @return self
     */
    public function setPeriodeDateDebut(DateTime $periodeDateDebut): self
    {
        $this->periodeDateDebut = $periodeDateDebut;
        return $this;
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
     * @param \DateTime $periodeDateFin
     * @return self
     */
    public function setPeriodeDateFin(DateTime $periodeDateFin): self
    {
        $this->periodeDateFin = $periodeDateFin;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPeriodeAnneeUniversitaire(): ?int
    {
        return $this->periodeAnneeUniversitaire;
    }

    /**
     * @param int|null $periodeAnneeUniversitaire
     * @return self
     */
    public function setPeriodeAnneeUniversitaire(?int $periodeAnneeUniversitaire): self
    {
        $this->periodeAnneeUniversitaire = $periodeAnneeUniversitaire;
        return $this;
    }

    /**
     * @return \Doctorant\Entity\Db\Doctorant
     */
    public function getDoctorant(): Doctorant
    {
        return $this->doctorant;
    }

    /**
     * @param \Doctorant\Entity\Db\Doctorant $doctorant
     * @return self
     */
    public function setDoctorant(Doctorant $doctorant): self
    {
        $this->doctorant = $doctorant;
        return $this;
    }

    /**
     * @return \Structure\Entity\Db\EcoleDoctorale
     */
    public function getEcoleDoctorale(): EcoleDoctorale
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @param \Structure\Entity\Db\EcoleDoctorale $ecoleDoctorale
     * @return self
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale): self
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
        return $this;
    }


    public function getResourceId(): string
    {
        return 'InscriptionAdministrative';
    }
}