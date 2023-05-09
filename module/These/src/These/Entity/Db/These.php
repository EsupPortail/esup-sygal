<?php

namespace These\Entity\Db;

use Application\Entity\AnneeUniv;
use Application\Entity\Db\Financement;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\Role;
use Application\Entity\Db\TitreAcces;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
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
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use These\Filter\TitreApogeeFilter;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

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

    public static $etatsLibelles = [
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
    private $etatThese;

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
     * @var string
     */
    private $codeSiseDiscipline;

    /**
     * @var string
     */
    private $libelleDiscipline;

    /**
     * @var DateTime
     */
    protected $datePrevisionSoutenance;

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
    private $codeUniteRecherche;

    /**
     * @var string
     */
    private $libelleUniteRecherche;

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
     * @var Collection
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
    private $propositions;

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
        $this->propositions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * Get histoModification
     *
     * @return \DateTime
     */
    public function getHistoModification()
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
     * @return string
     */
    public function getCodeSiseDiscipline()
    {
        return $this->codeSiseDiscipline;
    }

    /**
     * @param string $codeSiseDiscipline
     * @return self
     */
    public function setCodeSiseDiscipline($codeSiseDiscipline): self
    {
        $this->codeSiseDiscipline = $codeSiseDiscipline;
        return $this;
    }

    /**
     * @param string $libelleDiscipline
     * @return self
     */
    public function setLibelleDiscipline($libelleDiscipline)
    {
        $this->libelleDiscipline = $libelleDiscipline;

        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleDiscipline()
    {
        return $this->libelleDiscipline;
    }

    /**
     * @return DateTime
     */
    public function getDatePrevisionSoutenance()
    {
        return $this->datePrevisionSoutenance;
    }

    /**
     * @return string
     */
    public function getDatePrevisionSoutenanceToString()
    {
        return Util::formattedDate($this->getDatePrevisionSoutenance());
    }

    /**
     * @param DateTime $datePrevisionSoutenance
     * @return self
     */
    public function setDatePrevisionSoutenance(DateTime $datePrevisionSoutenance = null)
    {
        $this->datePrevisionSoutenance = $datePrevisionSoutenance;

        return $this;
    }

    /**
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
    public function getCodeUniteRecherche()
    {
        return $this->codeUniteRecherche;
    }

    /**
     * @param string $codeUniteRecherche
     * @return self
     */
    public function setCodeUniteRecherche($codeUniteRecherche)
    {
        $this->codeUniteRecherche = $codeUniteRecherche;

        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleUniteRecherche()
    {
        return $this->libelleUniteRecherche;
    }

    /**
     * @param string $libelleUniteRecherche
     * @return self
     */
    public function setLibelleUniteRecherche($libelleUniteRecherche)
    {
        $this->libelleUniteRecherche = $libelleUniteRecherche;

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
     * Retourne l'éventuelle ED liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.ecoleDoctorale' puis 'ecoleDoctorale.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.ecoleDoctorale'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'ED substituante s'il y en a une ; sinon l'ED d'origine.
     * @return EcoleDoctorale|null
     * @see EcoleDoctorale::getEcoleDoctoraleSubstituant()
     */
    public function getEcoleDoctorale(bool $returnSubstitIfExists = true): ?EcoleDoctorale
    {
        if ($returnSubstitIfExists && $this->ecoleDoctorale && ($sustitut = $this->ecoleDoctorale->getEcoleDoctoraleSubstituante())) {
            return $sustitut;
        }

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
     * Retourne l'éventuelle UR liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.uniteRecherche' puis 'uniteRecherche.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.uniteRecherche'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'UR substituante s'il y en a une ; sinon l'UR d'origine.
     * @see UniteRecherche::getUniteRechercheSubstituante()
     * @return UniteRecherche|null
     */
    public function getUniteRecherche(bool $returnSubstitIfExists = true): ?UniteRecherche
    {
        if ($returnSubstitIfExists && $this->uniteRecherche && ($sustitut = $this->uniteRecherche->getUniteRechercheSubstituante())) {
            return $sustitut;
        }

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
            return $this->acteurs->filter(function (Acteur $a) {
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

        usort($acteurs, $callable ?: Acteur::getComparisonFunction());

        return new ArrayCollection($acteurs);
    }

    /**
     * Retourne les acteurs de cette thèse dont le rôle est parmi ceux spécifiés.
     *
     * @param string|string[] $code
     * @return Collection
     */
    public function getActeursByRoleCode($code): Collection
    {
        $codes = (array) $code;
        $filter = function(Acteur $a) use ($codes) {
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
        $formatter = function(Acteur $a) use ($includeRole) {
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

        $individus = $this->getActeursByRoleCode($role)->map(function(Acteur $a) { return $a->getIndividu(); });

        return $individus->contains($individu);
    }

    /**
     * @param Acteur $acteur
     * @return $this
     */
    public function addActeur(Acteur $acteur)
    {
        $this->acteurs->add($acteur);
        return $this;
    }

    /**
     * @param Acteur $acteur
     * @return $this
     */
    public function removeActeur(Acteur $acteur)
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
     *
     * @param TypeValidation|string $type
     * @param bool                  $historisee
     * @return Collection
     */
    public function getValidations($type, $historisee = false)
    {
        if ($type instanceof TypeValidation) {
            $type = $type->getCode();
        }

        $validations = $this->validations;

        $validations = $validations->filter(function(Validation $v) use ($type) {
            return $v->getTypeValidation()->getCode() === $type;
        });
        $validations = $validations->filter(function(Validation $v) use ($historisee) {
            return $historisee === null || !$historisee === $v->estNonHistorise();
        });

        return $validations;
    }

    /**
     * Retourne l'éventuelle validation du type spécifié.
     *
     * @param TypeValidation|string $type
     * @param bool                  $historisee
     * @return Validation|null
     */
    public function getValidation($type, $historisee = false)
    {
        return $this->getValidations($type, $historisee)->first() ?: null;
    }

    /**
     * @param Validation $validation
     * @return These
     */
    public function addValidation(Validation $validation)
    {
        $this->validations->add($validation);

        return $this;
    }

    /**
     * @param Validation $validation
     * @return These
     */
    public function removeValidation(Validation $validation)
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
        return $this->libelleEtabCotutelle;
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
        return $this->libellePaysCotutelle;
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
     * Retourne l'éventuel établissement lié *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.etablissement' puis 'etablissement.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.etablissement'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'établissement substituant s'il y en a un ; sinon l'établissement d'origine.
     * @see Etablissement::getEtablissementSubstituant()
     * @return Etablissement|null
     */
    public function getEtablissement(bool $returnSubstitIfExists = true): ?Etablissement
    {
        if ($returnSubstitIfExists && $this->etablissement && ($sustitut = $this->etablissement->getEtablissementSubstituant())) {
            return $sustitut;
        }

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
     * @param int|null $anneeMax Année maximum à considérer.
     * Il s'agit de l'année "de début" d'une année universitaire, ex : N pour l'année universitaire N/N+1.
     * Si absente, c'est l'année universitaire courante qui est considérée.
     * @return int
     */
    public function getAnneesUnivInscriptionCount(?int $anneeMax = null) : int
    {
        if ($anneeMax === null) {
            $anneeMax = AnneeUniv::courante()->getPremiereAnnee();
        }
        $inscriptions = array_filter(
            $this->getAnneesUnivInscription()->toArray(),
            function (TheseAnneeUniv $a) use ($anneeMax) {
                return $a->getAnneeUniv() <= $anneeMax;
            }
        );
        return count($inscriptions);
    }

    /**
     * Retourne l'année universitaire de première inscription,
     *
     * @return TheseAnneeUniv|VTheseAnneeUnivFirst
     */
    public function getAnneeUniv1ereInscription()
    {
        // NB: le mapping de VTheseAnneeUnivFirst est un copier-coller de TheseAnneeUniv
        return $this->anneesUniv1ereInscription->first();
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
        return $this->financements->filter(fn(Financement $f) => $f->estNonHistorise());
    }

    /**
     * @return TitreAcces
     */
    public function getTitreAcces()
    {
        return $this->titreAcces->first() ?: null;
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
        $encadrements = $this->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE)->toArray();
        $emailExtractor = fn(Individu $i) => $i->getEmailPro() ?: $i->getEmailUtilisateur();

        /** @var Acteur $acteur */
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

        /** @var Acteur $acteur */
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
     * Retourne les mails des directeurs de thèse.
     * @return string|null
     */
    public function getPresidentJuryEmail() : ?string
    {
        $president = $this->getActeursByRoleCode(Role::CODE_PRESIDENT_JURY)->toArray();
        if (count($president) !== 1) throw new \RuntimeException("Nombre de président incorrect ...");
        $president = current($president);

        if ($president->getIndividu()->getEmail() !== null) return $president->getIndividu()->getEmail();
        if ($president->getMembre()->getEmail() !== null) return $president->getMembre()->getEmail();
        return null;
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
     * @return Acteur[]|Individu[]
     */
    public function getEncadrements($asIndividu = false)
    {
        /** @var Acteur[] $acteurs */
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
    public function getPropositions()
    {
        return $this->propositions;
    }

    /**
     * @param mixed $propositions
     * @return These
     */
    public function setPropositions($propositions)
    {
        $this->propositions = $propositions;
        return $this;
    }

    public function getPresidentJury(): ?Acteur
    {
        /** @var Acteur $acteur */
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
}
