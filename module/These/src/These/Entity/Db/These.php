<?php

namespace These\Entity\Db;

use Acteur\Entity\Db\ActeurThese;
use Application\Entity\AnneeUniv;
use Application\Entity\Db\Discipline;
use Application\Entity\Db\DomaineHal;
use Application\Entity\Db\Financement;
use Application\Entity\Db\Pays;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\Role;
use Application\Entity\Db\TitreAcces;
use DateTime;
use Depot\Entity\Db\Attestation;
use Depot\Entity\Db\Diffusion;
use Depot\Entity\Db\FichierThese;
use Depot\Entity\Db\MetadonneeThese;
use Depot\Entity\Db\RdvBu;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\PropositionThese;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use These\Filter\TitreApogeeFilter;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationThese;

/**
 * These
 */
class These implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    use TheseCorrectionAwareTrait;

    const RESOURCE_ID = 'These';

    const ETAT_EN_COURS   = 'E';
    const ETAT_ABANDONNEE = 'A';
    const ETAT_SOUTENUE   = 'S';
    const ETAT_TRANSFEREE = 'U';

    public static array $etatsLibelles = [
        self::ETAT_EN_COURS   => "En cours",
        self::ETAT_ABANDONNEE => "Abandonnée",
        self::ETAT_SOUTENUE   => "Soutenue",
        self::ETAT_TRANSFEREE => "Transférée",
    ];

    const RESULTAT_AJOURNE = 0;
    const RESULTAT_ADMIS   = 1;

    public static $resultatsLibellesLongs = [
        self::RESULTAT_AJOURNE => "Ajourné",
        self::RESULTAT_ADMIS   => "Admis",
    ];
    public static $resultatsLibellesCourts = [
        self::RESULTAT_AJOURNE => "AJ",
        self::RESULTAT_ADMIS   => "ADM",
    ];

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var string
     */
    private $titre;

    /**
     * @var string
     */
    private string $etatThese = self::ETAT_EN_COURS;

    /**
     * @var null|int
     */
    private $resultat = null;

    /**
     * @var DateTime
     */
    private $datePremiereInscription;

    /**
     * NB: N'est plus mappée à une colonne.
     *
     * @var integer
     */
    private $anneeUniv1ereInscription;

    /**
     * @var DateTime
     */
    protected $dateSoutenance;

    /**
     * @var DateTime
     */
    protected $dateFinConfidentialite;

    /**
     * @var DateTime|null
     */
    protected $dateAbandon;

    /**
     * @var DateTime|null
     */
    protected $dateTransfert;

    /**
     * @var string
     */
    private $libelleEtabCotutelle;

    /**
     * @var string
     */
    private $libellePaysCotutelle;

    /**
     * @var string
     */
    private $soutenanceAutorisee;

    /**
     * Eventuel forçage *pour cette thèse en particulier* du paramètre global
     * {@see \Depot\Service\These\DepotService::$resaisirAutorisationDiffusionVersionCorrigee}.
     */
    private ?bool $resaisirAutorisationDiffusionDepotVersionCorrigee = null;

    /**
     * Eventuel forçage *pour cette thèse en particulier* du paramètre global
     * {@see \Depot\Service\These\DepotService::$resaisirAttestationsVersionCorrigee}.
     */
    private ?bool $resaisirAttestationsDepotVersionCorrigee = null;

    /**
     * @var Doctorant
     */
    private $doctorant;

    /**
     * @var EcoleDoctorale
     */
    private $ecoleDoctorale;

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;

    /**
     * @var Collection
     */
    private $fichierTheses;

    /**
     * @var Collection
     */
    private $metadonnees;

    /**
     * @var Collection
     */
    private $attestations;

    /**
     * @var Collection
     */
    private $miseEnLignes;

    /**
     * @var ArrayCollection
     */
    private $acteurs;

    /**
     * @var Collection
     */
    private $rdvBus;

    /**
     * @var Collection
     */
    private $validations;

    /**
     * @var TitreAcces
     */
    private $titreAcces;

    /**
     * @var TitreApogeeFilter
     */
    private $titreFilter;

    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @var ArrayCollection
     */
    private $financements;

    /**
     * @var ArrayCollection
     */
    private $anneesUnivInscription;

    /**
     * @var ArrayCollection
     */
    private $anneesUniv1ereInscription;

    /**
     * @var ArrayCollection
     */
    private $rapports;

    /**
     * @var ArrayCollection
     */
    private $propositionsThese;

    /**
     * @var ArrayCollection
     */
    private $domainesHal;

    private ?Discipline $discipline = null;

    /**
     * @var Etablissement
     */
    private $etablissementCoTutelle;

    /**
     * @var Pays
     */
    private $paysCoTutelle;

    /**
     * @return TitreApogeeFilter
     */
    public function getTitreFilter()
    {
        if ($this->titreFilter === null) {
            $this->titreFilter = new TitreApogeeFilter();
        }
        return $this->titreFilter;
    }

    /**
     * 
     */
    public function __construct()
    {
        $this->fichierTheses = new ArrayCollection();
        $this->metadonnees = new ArrayCollection();
        $this->attestations = new ArrayCollection();
        $this->miseEnLignes = new ArrayCollection();
        $this->acteurs = new ArrayCollection();
        $this->rdvBus = new ArrayCollection();
        $this->anneesUnivInscription = new ArrayCollection();
        $this->anneesUniv1ereInscription = new ArrayCollection();
        $this->rapports = new ArrayCollection();
        $this->propositionsThese = new ArrayCollection();
        $this->domainesHal = new ArrayCollection();
        $this->validations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->id ?: "";
    }

    /**
     * Get histoModification
     */
    public function getHistoModification(): ?DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
     *
     * @return self
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode
     *
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     */
    public function getTitre()
    {
        return $this->getTitreFilter()->filter($this->titre);
    }

    /**
     * @param string $titre
     * @return self
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Set discipline.
     *
     * @param Discipline|null $discipline
     *
     * @return These
     */
    public function setDiscipline(Discipline $discipline = null): static
    {
        $this->discipline = $discipline;

        return $this;
    }

    /**
     * Get discipline.
     *
     * @return Discipline|null
     */
    public function getDiscipline(): ?Discipline
    {
        return $this->discipline;
    }

    /**so
     * @return DateTime|null
     */
    public function getDateSoutenance(): ?DateTime
    {
        return $this->dateSoutenance;
    }

    /**
     * @return string
     */
    public function getDateSoutenanceToString(): string
    {
        return Util::formattedDate($this->getDateSoutenance());
    }

    /**
     * @param \DateTime|null $dateSoutenance
     * @return self
     */
    public function setDateSoutenance(?DateTime $dateSoutenance = null): self
    {
        $this->dateSoutenance = $dateSoutenance;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateFinConfidentialite()
    {
        return $this->dateFinConfidentialite;
    }

    /**
     * @return bool
     */
    public function etaitConfidentielle()
    {
        if ($this->getDateFinConfidentialite() === null) {
            return false;
        }

        return $this->getDateFinConfidentialite()->setTime(0, 0, 0) < (new DateTime())->setTime(0, 0, 0);
    }

    /**
     * @return bool
     */
    public function estConfidentielle()
    {
        if ($this->getDateFinConfidentialite() === null) {
            return false;
        }

        return $this->getDateFinConfidentialite()->setTime(0, 0, 0) >= (new DateTime())->setTime(0, 0, 0);
    }

    /**
     * @return string
     */
    public function getDateFinConfidentialiteToString()
    {
        return Util::formattedDate($this->getDateFinConfidentialite());
    }

    /**
     * @param DateTime $dateFinConfidentialite
     * @return self
     */
    public function setDateFinConfidentialite(DateTime $dateFinConfidentialite = null)
    {
        $this->dateFinConfidentialite = $dateFinConfidentialite;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateAbandon()
    {
        return $this->dateAbandon;
    }

    /**
     * @return string
     */
    public function getDateAbandonToString(): string
    {
        return Util::formattedDate($this->getDateAbandon());
    }

    /**
     * @param DateTime|null $dateAbandon
     * @return These
     */
    public function setDateAbandon(DateTime $dateAbandon = null): These
    {
        $this->dateAbandon = $dateAbandon;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateTransfert()
    {
        return $this->dateTransfert;
    }

    /**
     * @return string
     */
    public function getDateTransfertToString(): string
    {
        return Util::formattedDate($this->getDateTransfert());
    }

    /**
     * @param DateTime|null $dateTransfert
     * @return These
     */
    public function setDateTransfert(DateTime $dateTransfert = null): These
    {
        $this->dateTransfert = $dateTransfert;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoutenanceAutorisee()
    {
        return $this->soutenanceAutorisee;
    }

    /**
     * @param string $soutenanceAutorisee
     */
    public function setSoutenanceAutorisee($soutenanceAutorisee)
    {
        $this->soutenanceAutorisee = $soutenanceAutorisee;
    }

    public function getResaisirAutorisationDiffusionDepotVersionCorrigee(): ?bool
    {
        return $this->resaisirAutorisationDiffusionDepotVersionCorrigee;
    }

    public function setResaisirAutorisationDiffusionDepotVersionCorrigee(?bool $resaisirAutorisationDiffusionDepotVersionCorrigee): self
    {
        $this->resaisirAutorisationDiffusionDepotVersionCorrigee = $resaisirAutorisationDiffusionDepotVersionCorrigee;
        return $this;
    }

    public function getResaisirAttestationsDepotVersionCorrigee(): ?bool
    {
        return $this->resaisirAttestationsDepotVersionCorrigee;
    }

    public function setResaisirAttestationsDepotVersionCorrigee(?bool $resaisirAttestationsDepotVersionCorrigee): self
    {
        $this->resaisirAttestationsDepotVersionCorrigee = $resaisirAttestationsDepotVersionCorrigee;
        return $this;
    }

    /**
     * @return Doctorant
     */
    public function getDoctorant()
    {
        return $this->doctorant;
    }

    /**
     * @param Doctorant $doctorant
     * @return self
     */
    public function setDoctorant($doctorant)
    {
        $this->doctorant = $doctorant;

        return $this;
    }

    /**
     * Retourne l'éventuelle ED liée.
     */
    public function getEcoleDoctorale(): ?EcoleDoctorale
    {
        return $this->ecoleDoctorale;
    }

    /**
     * Change l'ED liée à cette thèse.
     *
     * @param EcoleDoctorale $ecoleDoctorale
     * @return self
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale): self
    {
        $this->ecoleDoctorale = $ecoleDoctorale;

        return $this;
    }

    /**
     * Retourne l'éventuelle UR liée.
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        return $this->uniteRecherche;
    }

    /**
     * @param UniteRecherche $uniteRecherche
     * @return self
     */
    public function setUniteRecherche($uniteRecherche)
    {
        $this->uniteRecherche = $uniteRecherche;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFichierTheses()
    {
        return $this->fichierTheses;
    }
    
    /**
     * @param FichierThese $fichierThese
     * @return $this
     */
    public function addFichierThese(FichierThese $fichierThese)
    {
        $this->fichierTheses->add($fichierThese);

        return $this;
    }

    /**
     * @param FichierThese $fichierThese
     * @return $this
     */
    public function removeFichierThese(FichierThese $fichierThese)
    {
        $this->fichierTheses->removeElement($fichierThese);

        return $this;
    }

    /**
     * @param Fichier $fichier
     * @return $this
     */
    public function removeFichier(Fichier $fichier)
    {
        $fichierThese = $this->fichierTheses->filter(function(FichierThese $ft) use ($fichier) {
            return $ft->getFichier() === $fichier;
        })->first();
        if (! $fichierThese) {
            throw new RuntimeException("Le fichier à supprimer est introuvable parmi les fichiers de la thèse");
        }

        $this->removeFichierThese($fichierThese);

        return $this;
    }

    /**
     * @return MetadonneeThese|null
     */
    public function getMetadonnee()
    {
        return $this->metadonnees->first() ?: null;
    }

    /**
     * @return Collection
     */
    public function getMetadonnees()
    {
        return $this->metadonnees;
    }

    /**
     * @param MetadonneeThese $metadonnee
     * @return $this
     */
    public function addMetadonnee(MetadonneeThese $metadonnee)
    {
        $this->metadonnees->add($metadonnee);
        return $this;
    }

    /**
     * @param MetadonneeThese $metadonnee
     * @return $this
     */
    public function removeMetadonnee(MetadonneeThese $metadonnee)
    {
        $this->metadonnees->removeElement($metadonnee);
        return $this;
    }

    /**
     * @param bool $historisee
     * @return Collection
     */
    public function getAttestations($historisee = false)
    {
        $attestations = $this->attestations;

        $attestations = $attestations->filter(function(Attestation $d) use ($historisee) {
            return $historisee === null || !$historisee === $d->estNonHistorise();
        });

        return $attestations;
    }

    /**
     * @param bool $historisee
     * @return Attestation|null
     */
    public function getAttestation($historisee = false)
    {
        return $this->getAttestations($historisee)->first() ?: null;
    }

    /**
     * Retourne l'éventuelle Attestation concernant la version de fichier spécifiée.
     *
     * @param VersionFichier $version
     * @return Attestation|null
     */
    public function getAttestationForVersion(VersionFichier $version)
    {
        /** @var Attestation $attestation */
        foreach ($this->attestations as $attestation) {
            if ($attestation->estHistorise()) {
                continue;
            }
            if ($version->estVersionCorrigee() === $attestation->getVersionCorrigee()) {
                return $attestation;
            }
        }

        return null;
    }

    /**
     * @param Attestation $attestation
     * @return $this
     */
    public function addAttestation(Attestation $attestation)
    {
        $this->attestations->add($attestation);
        return $this;
    }

    /**
     * @param Attestation $attestation
     * @return $this
     */
    public function removeAttestation(Attestation $attestation)
    {
        $this->attestations->removeElement($attestation);
        return $this;
    }

    /**
     * Retourne l'éventuelle Diffusion concernant la version de fichier spécifiée.
     *
     * @param VersionFichier $version
     * @return Diffusion|null
     */
    public function getDiffusionForVersion(VersionFichier $version)
    {
        /** @var Diffusion $diffusion */
        foreach ($this->miseEnLignes as $diffusion) {
            if ($diffusion->estHistorise()) {
                continue;
            }
            if ($version->estVersionCorrigee() === $diffusion->getVersionCorrigee()) {
                return $diffusion;
            }
        }

        return null;
    }

    /**
     * @param Diffusion $miseEnLigne
     * @return $this
     */
    public function addDiffusion(Diffusion $miseEnLigne)
    {
        $this->miseEnLignes->add($miseEnLigne);
        return $this;
    }

    /**
     * @param Diffusion $miseEnLigne
     * @return $this
     */
    public function removeDiffusion(Diffusion $miseEnLigne)
    {
        $this->miseEnLignes->removeElement($miseEnLigne);
        return $this;
    }

    /**
     * @param bool $exceptHistorises
     * @return Collection
     */
    public function getActeurs($exceptHistorises = true)
    {
        if ($exceptHistorises) {
            return $this->acteurs->filter(function (ActeurThese $a) {
                return null === $a->getHistoDestruction();
            });
        }

        return $this->acteurs;
    }

    /**
     * @param callable $callable
     * @return Collection
     */
    public function getActeursSorted($callable = null)
    {
        $acteurs = $this->getActeurs()->toArray();

        usort($acteurs, $callable ?: ActeurThese::getComparisonFunction());

        return new ArrayCollection($acteurs);
    }

    /**
     * Retourne les acteurs de cette thèse dont le rôle est parmi ceux spécifiés
     *
     * @param string|string[] $code
     * @return Collection
     */
    public function getActeursByRoleCode($code): Collection
    {
        $codes = (array) $code;
        $filter = function(ActeurThese $a) use ($codes) {
            return in_array($a->getRole()->getCode(), $codes);
        };

        return $this->getActeurs()->filter($filter);
    }

    /**
     * @param bool $includeRole
     * @param string $separator
     * @return string
     */
    public function getActeursToString($includeRole = false, $separator = ", ")
    {
        $formatter = function(ActeurThese $a) use ($includeRole) {
            $str = (string) $a;
            if ($includeRole) {
                $str .= " ({$a->getRole()->getRoleId()})";
            }
            return $str;
        };

        return implode($separator, array_map($formatter, $this->getActeurs()->toArray()));
    }

    /**
     * Teste si un individu fait partie des acteurs au titre d'un rôle particulier.
     *
     * @param Individu    $individu
     * @param string|Role $role
     * @return bool
     */
    public function hasActeurWithRole(Individu $individu, $role): bool
    {
        if ($role instanceof Role) {
            $role = $role->getCode();
        }

        $individus = $this->getActeursByRoleCode($role)->map(function(ActeurThese $a) { return $a->getIndividu(); });

        return $individus->contains($individu);
    }

    /**
     * @param ActeurThese $acteur
     * @return $this
     */
    public function addActeur(ActeurThese $acteur)
    {
        $this->acteurs->add($acteur);
        return $this;
    }

    /**
     * @param ActeurThese $acteur
     * @return $this
     */
    public function removeActeur(ActeurThese $acteur)
    {
        $this->acteurs->removeElement($acteur);
        return $this;
    }

    /**
     * @return RdvBu|null
     */
    public function getRdvBu()
    {
        return $this->rdvBus->first() ?: null;
    }

    /**
     * @param \Depot\Entity\Db\RdvBu $rdvBu
     * @return $this
     */
    public function addRdvBu(\Depot\Entity\Db\RdvBu $rdvBu)
    {
        $this->rdvBus->add($rdvBu);
        return $this;
    }

    /**
     * @param RdvBu $rdvBu
     * @return $this
     */
    public function removeRdvBu(RdvBu $rdvBu)
    {
        $this->rdvBus->removeElement($rdvBu);
        return $this;
    }

    /**
     * Retourne les éventuelles validations du type spécifié.
     */
    public function getValidations(string|TypeValidation|null $type = null, bool $historisee = false): Collection
    {
        if ($type instanceof TypeValidation) {
            $type = $type->getCode();
        }

        $validations = $this->validations;
        $validations = $validations->filter(function(ValidationThese $v) use ($type) {
            return $v->getValidation()->getTypeValidation()->getCode() === $type;
        });
        $validations = $validations->filter(function (ValidationThese $v) use ($historisee) {
            return !$historisee === $v->estNonHistorise();
        });

        return $validations;
    }

    /**
     * Retourne l'éventuelle validation du type spécifié.
     */
    public function getValidation(string|TypeValidation $type, bool $historisee = false): ?ValidationThese
    {
        return $this->getValidations($type, $historisee)->first() ?: null;
    }

    /**
     * @param ValidationThese $validation
     * @return These
     */
    public function addValidation(ValidationThese $validation)
    {
        $this->validations->add($validation);

        return $this;
    }

    /**
     * @param ValidationThese $validation
     * @return These
     */
    public function removeValidation(ValidationThese $validation)
    {
        $this->validations->removeElement($validation);

        return $this;
    }

    /**
     * @return string
     */
    public function getEtatThese()
    {
        return $this->etatThese;
    }

    /**
     * @return string
     */
    public function getEtatTheseToString()
    {
        return self::$etatsLibelles[$this->etatThese];
    }

    /**
     * @param string $etatThese
     * @return self
     */
    public function setEtatThese($etatThese)
    {
        $this->etatThese = $etatThese;

        return $this;
    }

    /**
     * Retourne :
     * - <code>1</code> si "admis" ;
     * - <code>0</code> si "ajourné" ;
     * - <code>null</code> si inconnu.
     *
     * @return null|int
     */
    public function getResultat()
    {
        return $this->resultat;
    }

    /**
     * Retourne "ADM"/"Admis", "AJ"/"Ajourné" ou "".
     *
     * @param bool $short
     * @return string
     */
    public function getResultatToString($short = false)
    {
        if (null === $this->getResultat()) {
            return "";
        }

        $strings = $short ? self::$resultatsLibellesCourts : self::$resultatsLibellesLongs;

        return $strings[$this->resultat];
    }

    /**
     * @param null|int $resultat
     * @return self
     */
    public function setResultat($resultat = null)
    {
        $this->resultat = $resultat;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDatePremiereInscription()
    {
        return $this->datePremiereInscription;
    }

    /**
     * Calcule la durée de la thèse en mois.
     *
     * @return float
     */
    public function getDureeThese()
    {
        if (! $this->getDateSoutenance()) {
            throw new LogicException("Aucune date de soutenance renseignée");
        }
        if (! $this->getDatePremiereInscription()) {
            throw new LogicException("Aucune date de première inscription renseignée");
        }

        return $this->getDateSoutenance()->diff($this->getDatePremiereInscription())->format('%a') / 30.5;
    }

    /**
     * @return string
     */
    public function getDatePremiereInscriptionToString()
    {
        return Util::formattedDate($this->getDatePremiereInscription());
    }

    /**
     * @param DateTime $date
     * @return self
     */
    public function setPremiereInscription(DateTime $date = null)
    {
        $this->datePremiereInscription = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleEtabCotutelle()
    {
        $estImportable = $this->getSource()->getImportable();
        return $estImportable ? $this->libelleEtabCotutelle : $this->getEtablissementCoTutelle();
    }

    /**
     * @param string $libelleEtabCotutelle
     * @return These
     */
    public function setLibelleEtabCotutelle($libelleEtabCotutelle)
    {
        $this->libelleEtabCotutelle = $libelleEtabCotutelle;

        return $this;
    }

    /**
     * @return string
     */
    public function getLibellePaysCotutelle()
    {
        $estImportable = $this->getSource()->getImportable();

        return $estImportable ? $this->libellePaysCotutelle : $this->getPaysCoTutelle();
    }

    /**
     * @param string $libellePaysCotutelle
     * @return These
     */
    public function setLibellePaysCotutelle($libellePaysCotutelle)
    {
        $this->libellePaysCotutelle = $libellePaysCotutelle;

        return $this;
    }

    /**
     * Retourne l'éventuel établissement lié.
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
    }

    /**
     * @return ArrayCollection
     */
    public function getAnneesUnivInscription(): ArrayCollection
    {
        return $this->anneesUnivInscription->filter(fn(TheseAnneeUniv $tau) => $tau->estNonHistorise());
    }

    /**
     * Retourne les années universitaires d'inscription séparées par une virgule.
     *
     * @param string $glue Séparateur, ex: ' - '
     * @return string Ex: "2015/2016, 2016/2017, 2017/2018"
     */
    public function getAnneesUnivInscriptionToString(string $glue = ', '): string
    {
        $anneesUnivs = array_map(
            fn(TheseAnneeUniv $tau) => $tau->getAnneeUnivToString(),
            array_filter(
                $this->anneesUnivInscription->toArray(),
                fn(TheseAnneeUniv $tau) => $tau->estNonHistorise()
            )
        );
        sort($anneesUnivs);

        return implode($glue, $anneesUnivs);
    }

    /**
     * Retourne le nombre d'années d'inscriptions liées à cette thèse.
     *
     * @param \Application\Entity\AnneeUniv $anneeUnivMax Année universitaire maximum à considérer.
     * @return int
     */
    public function getAnneesUnivInscriptionCount(AnneeUniv $anneeUnivMax) : int
    {
        $inscriptions = array_filter(
            $this->getAnneesUnivInscription()->toArray(),
            function (TheseAnneeUniv $a) use ($anneeUnivMax) {
                return $a->getAnneeUniv() <= $anneeUnivMax->getPremiereAnnee();
            }
        );
        return count($inscriptions);
    }

    /**
     * Retourne l'année universitaire de première inscription,
     *
     * @return TheseAnneeUniv|VTheseAnneeUnivFirst|null
     */
    public function getAnneeUniv1ereInscription()
    {
        // NB: le mapping de VTheseAnneeUnivFirst est un copier-coller de TheseAnneeUniv
        return $this->anneesUniv1ereInscription->first() ?: null;
    }

    /**
     * @return Collection
     */
    public function getRapports()
    {
        return $this->rapports;
    }

    /**
     * @param Rapport $rapport
     * @return self
     */
    public function addRapport(Rapport $rapport)
    {
        $this->rapports->add($rapport);

        return $this;
    }

    /**
     * @param Rapport $rapport
     * @return self
     */
    public function removeRapport(Rapport $rapport)
    {
        $this->rapports->removeElement($rapport);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFinancements()
    {
        // Solution de filtrage temporaire (TODO : Filtrer en amont dans la requête)
        if (is_array($this->financements)) {
            $this->financements = new ArrayCollection($this->financements);
        }
        return $this->financements ? $this->financements->filter(fn(Financement $f) => $f->estNonHistorise()) : new ArrayCollection();
    }

    /**
     * @return TitreAcces|null
     */
    public function setTitreAcces(TitreAcces $titreAcces)
    {
        return $this->titreAcces = $titreAcces;
    }

    /**
     * @return TitreAcces|null
     */
    public function getTitreAcces()
    {
//        return $this->titreAcces ? is_array($this->titreAcces) ? $this->titreAcces[0] : $this->titreAcces->first() : null ;
        return $this->titreAcces;
    }

    /**
     * Retourne les mails des directeurs de thèse.
     *
     * @param Individu[] $individusSansMail Liste des individus sans mail, format: "Paul Hochon" => Individu
     * @return array
     */
    public function getDirecteursTheseEmails(array &$individusSansMail = []): array
    {
        $emails = [];
        /** @var ActeurThese[] $directeurs */
        $directeurs = $this->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE)->toArray();
        /** @var ActeurThese[] $codirecteurs */
        $codirecteurs = $this->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE)->toArray();
        $encadrements = array_merge($directeurs, $codirecteurs);
        $emailExtractor = fn(Individu $i) => $i->getEmailPro() ?: $i->getEmailUtilisateur();

        /** @var ActeurThese $acteur */
        foreach ($encadrements as $acteur) {
            $individu = $acteur->getIndividu();
            $email = $emailExtractor->__invoke($individu);
            $name = (string) $individu;
            if (! $email) {
                $individusSansMail[$name] = $individu;
            } else {
                $emails[$email] = $name;
            }
        }

        return $emails;
    }

    /**
     * Retourne les mails des codirecteurs de thèse.
     *
     * @param Individu[] $individusSansMail Liste des individus sans mail, format: "Paul Hochon" => Individu
     * @return array
     */
    public function getCoDirecteursTheseEmails(array &$individusSansMail = []): array
    {
        $emailsPros = [];
        $encadrements = $this->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE)->toArray();
        $emailExtractor = fn(Individu $i) => $i->getEmailPro() ?: $i->getEmailUtilisateur();

        /** @var ActeurThese $acteur */
        foreach ($encadrements as $acteur) {
            $individu = $acteur->getIndividu();
            $email = $emailExtractor->__invoke($individu);
            $name = (string) $individu;
            if (! $email) {
                $individusSansMail[$name] = $individu;
            } else {
                $emails[$email] = $name;
            }
        }

        return $emailsPros;
    }

    /**
     * Retourne le mail du président du jury de thèse.
     *
     * @return string|null
     */
    public function getPresidentJuryEmail() : ?string
    {
        /** @var \These\Entity\Db\Acteur[] $presidents */
        $presidents = $this->getActeursByRoleCode(Role::CODE_PRESIDENT_JURY)->toArray();
        if (count($presidents) !== 1) throw new \RuntimeException("Nombre de président incorrect ...");
        $president = current($presidents);

        return
            $president->getIndividu()->getEmailPro() ?:
            $president->getIndividu()->getEmailContact() ?:
            $president->getIndividu()->getEmailUtilisateur() ?:
            $president->getMembre()->getEmail() ?:
            null;
    }

    /**
     * Predicat testant si une thèse est soutenue en utilisant la date du système
     * @return boolean
     */
    public function estSoutenue()
    {
        $soutenance = $this->getDateSoutenance();
        if ($soutenance === null)
        {
            return false;
        }
        $maintenant = new DateTime();
        if ($maintenant > $soutenance)
        {
            return true;
        }
        return false;
    }

    public function hasAnnexe()
    {
        /** @var FichierThese $fichier */
        foreach ($this->fichierTheses as $fichier) {
            if ($fichier->getFichier()->getNature() === NatureFichier::CODE_FICHIER_NON_PDF) return true;
        }
        return false;
    }

    public function hasMemoire()
    {
        /** @var FichierThese $fichier */
        foreach ($this->fichierTheses as $fichier) {
            if ($fichier->getFichier()->getNature() === NatureFichier::CODE_THESE_PDF) return true;
        }
        return false;
    }

    public function hasVersionInitiale() {

        /** @var FichierThese $fichier */
        foreach ($this->fichierTheses as $fichierThese) {
            /** @var Fichier $fichier */
            $fichier = $fichierThese->getFichier();
            $nature = $fichier->getNature()->getCode();
            $version = $fichier->getVersion()->getCode();
            if ($fichier->getHistoDestruction() === null
                && $nature === NatureFichier::CODE_THESE_PDF
                && $version === VersionFichier::CODE_ORIG)
                    return $fichierThese;
        }
        return null;
    }

    public function hasVersionCorrigee() {
        /** @var FichierThese $fichier */
        foreach ($this->fichierTheses as $fichier) {
            if ($fichier->getFichier()->getHistoDestruction() === null
                && $fichier->getFichier()->getNature() === NatureFichier::CODE_THESE_PDF
                && $fichier->getFichier()->getVersion() === VersionFichier::CODE_ORIG_CORR)
                    return $fichier;
        }
        return null;
    }

    /**
     * @param boolean $asIndividu
     * @return ActeurThese[]|Individu[]
     */
    public function getEncadrements($asIndividu = false)
    {
        /** @var ActeurThese[] $acteurs */
        $acteurs = [];

        $directeurs     = $this->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        foreach ($directeurs as $directeur) $acteurs[] = $directeur;
        $codirecteurs   = $this->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        foreach ($codirecteurs as $codirecteur) $acteurs[] = $codirecteur;

        if ($asIndividu === true) {
            $individus = [];
            foreach ($acteurs as $acteur) {
                $individus[] = $acteur->getIndividu();
            }
            return $individus;
        }

        return $acteurs;
    }

    /**
     * @return mixed
     */
    public function getPropositionsThese()
    {
        return $this->propositionsThese;
    }

    /**
     * @param mixed $propositions
     * @return These
     */
    public function setPropositionsThese($propositions)
    {
        $this->propositionsThese = $propositions;
        return $this;
    }

    /**
     * Add proposition.
     *
     * @param PropositionThese $propositionThese
     *
     * @return These
     */
    public function addPropositionThese(PropositionThese $propositionThese)
    {
        $this->propositionsThese[] = $propositionThese;

        return $this;
    }

    /**
     * Remove proposition.
     *
     * @param PropositionThese $propositionThese
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePropositionThese(PropositionThese $propositionThese)
    {
        return $this->propositionsThese->removeElement($propositionThese);
    }

    public function getPresidentJury(): ?ActeurThese
    {
        /** @var ActeurThese $acteur */
        foreach ($this->getActeurs() as $acteur) {
            if ($acteur->estNonHistorise() AND $acteur->getRole()->getCode() === Role::CODE_PRESIDENT_JURY) {
                return $acteur;
            }
        }
        return null;
    }

    public function getNbInscription() : int
    {
        return $this->getAnneesUnivInscription()->count();
    }

    /** Fonctions pour macros *****************************************************************************************/

    /**
     * @noinspection PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \These\Renderer\TheseTemplateVariable}
     */
    public function toStringEncadrement() : string {
        /** @var Individu[] $encadrement */
        $encadrement = $this->getEncadrements(true);
        $texte = [];
        foreach ($encadrement as $directeur) { $texte[] = $directeur->getNomComplet();}
        return implode (" et ", $texte);
    }

    /**
     * Set datePremiereInscription.
     *
     * @param \DateTime|null $datePremiereInscription
     *
     * @return These
     */
    public function setDatePremiereInscription($datePremiereInscription = null)
    {
        $this->datePremiereInscription = $datePremiereInscription;

        return $this;
    }

    /**
     * Add fichierThesis.
     *
     * @param \Depot\Entity\Db\FichierThese $fichierThesis
     *
     * @return These
     */
    public function addFichierThesis(\Depot\Entity\Db\FichierThese $fichierThesis)
    {
        $this->fichierTheses[] = $fichierThesis;

        return $this;
    }

    /**
     * Remove fichierThesis.
     *
     * @param \Depot\Entity\Db\FichierThese $fichierThesis
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFichierThesis(\Depot\Entity\Db\FichierThese $fichierThesis)
    {
        return $this->fichierTheses->removeElement($fichierThesis);
    }

    /**
     * Add miseEnLigne.
     *
     * @param \Depot\Entity\Db\Diffusion $miseEnLigne
     *
     * @return These
     */
    public function addMiseEnLigne(\Depot\Entity\Db\Diffusion $miseEnLigne)
    {
        $this->miseEnLignes[] = $miseEnLigne;

        return $this;
    }

    /**
     * Remove miseEnLigne.
     *
     * @param \Depot\Entity\Db\Diffusion $miseEnLigne
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeMiseEnLigne(\Depot\Entity\Db\Diffusion $miseEnLigne)
    {
        return $this->miseEnLignes->removeElement($miseEnLigne);
    }

    /**
     * Get miseEnLignes.
     *
     * @return Collection
     */
    public function getMiseEnLignes()
    {
        return $this->miseEnLignes;
    }

    /**
     * Get rdvBus.
     *
     * @return Collection
     */
    public function getRdvBus()
    {
        return $this->rdvBus;
    }

    /**
     * Add titreAcce.
     *
     * @param \Application\Entity\Db\TitreAcces $titreAcce
     *
     * @return These
     */
    public function addTitreAcce(\Application\Entity\Db\TitreAcces $titreAcce)
    {
        $this->titreAcces[] = $titreAcce;

        return $this;
    }

    /**
     * Remove titreAcce.
     *
     * @param \Application\Entity\Db\TitreAcces $titreAcce
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeTitreAcce(\Application\Entity\Db\TitreAcces $titreAcce)
    {
        return $this->titreAcces->removeElement($titreAcce);
    }

    /**
     * Add anneesUnivInscription.
     *
     * @param \These\Entity\Db\TheseAnneeUniv $anneesUnivInscription
     *
     * @return These
     */
    public function addAnneesUnivInscription(\These\Entity\Db\TheseAnneeUniv $anneesUnivInscription)
    {
        $this->anneesUnivInscription[] = $anneesUnivInscription;

        return $this;
    }

    /**
     * Remove anneesUnivInscription.
     *
     * @param TheseAnneeUniv $anneesUnivInscription
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAnneesUnivInscription(TheseAnneeUniv $anneesUnivInscription)
    {
        return $this->anneesUnivInscription->removeElement($anneesUnivInscription);
    }

    /**
     * Add anneesUniv1ereInscription.
     *
     * @param VTheseAnneeUnivFirst $anneesUniv1ereInscription
     *
     * @return These
     */
    public function addAnneesUniv1ereInscription(VTheseAnneeUnivFirst $anneesUniv1ereInscription)
    {
        $this->anneesUniv1ereInscription[] = $anneesUniv1ereInscription;

        return $this;
    }

    /**
     * Remove anneesUniv1ereInscription.
     *
     * @param VTheseAnneeUnivFirst $anneesUniv1ereInscription
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAnneesUniv1ereInscription(VTheseAnneeUnivFirst $anneesUniv1ereInscription)
    {
        return $this->anneesUniv1ereInscription->removeElement($anneesUniv1ereInscription);
    }

    /**
     * Get anneesUniv1ereInscription.
     *
     * @return Collection
     */
    public function getAnneesUniv1ereInscription()
    {
        return $this->anneesUniv1ereInscription;
    }

    /**
     * Add financement.
     *
     * @param Collection $financements
     *
     * @return These
     */
    public function addFinancements(Collection $financements)
    {
        foreach ($financements as $f) {
            $f->setThese($this);
            $this->financements[] = $f;
        }

        return $this;
    }

    /**
     * Remove financement.
     *
     * @param Collection $financements
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFinancements(Collection $financements)
    {
        foreach ($financements as $f) {
            $this->financements->removeElement($f);
        }
    }

    /**
     * Add proposition.
     *
     * @param \Soutenance\Entity\Proposition $proposition
     *
     * @return These
     */
    public function addProposition(\Soutenance\Entity\Proposition $proposition)
    {
        $this->propositions[] = $proposition;

        return $this;
    }

    /**
     * Remove proposition.
     *
     * @param \Soutenance\Entity\Proposition $proposition
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProposition(\Soutenance\Entity\Proposition $proposition)
    {
        return $this->propositions->removeElement($proposition);
    }

    /**
     * Add domainesHal.
     *
     * @param DomaineHal $domainesHal
     *
     * @return These
     */
    public function addDomainesHal(DomaineHal $domainesHal)
    {
        $this->domainesHal[] = $domainesHal;

        return $this;
    }

    /**
     * Remove domainesHal.
     *
     * @param DomaineHal $domainesHal
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeDomainesHal(DomaineHal $domainesHal)
    {
        return $this->domainesHal->removeElement($domainesHal);
    }

    /**
     * Get domainesHal.
     *
     * @return ArrayCollection
     */
    public function getDomainesHal()
    {
        return $this->domainesHal;
    }

    /**
     * Set etablissementCoTutelle.
     *
     * @param Etablissement|null $etablissementCoTutelle
     *
     * @return These
     */
    public function setEtablissementCoTutelle(Etablissement $etablissementCoTutelle = null)
    {
        $this->etablissementCoTutelle = $etablissementCoTutelle;

        return $this;
    }

    /**
     * Get etablissementCoTutelle.
     *
     * @return Etablissement|null
     */
    public function getEtablissementCoTutelle()
    {
        return $this->etablissementCoTutelle;
    }

    /**
     * Set paysCoTutelle.
     *
     * @param Pays|null $paysCoTutelle
     *
     * @return These
     */
    public function setPaysCoTutelle(Pays $paysCoTutelle = null)
    {
        $this->paysCoTutelle = $paysCoTutelle;

        return $this;
    }

    /**
     * Get paysCoTutelle.
     *
     * @return Pays|null
     */
    public function getPaysCoTutelle()
    {
        return $this->paysCoTutelle;
    }

    public function getApprenant(): Doctorant
    {
        return $this->getDoctorant();
    }
}
