<?php

namespace Application\Entity\Db;

use Application\Filter\TitreApogeeFilter;
use Assert\Assertion;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * These
 */
class These implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    
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

    const CORRECTION_AUTORISEE_OBLIGATOIRE = 'obligatoire';
    const CORRECTION_AUTORISEE_FACULTATIVE = 'facultative';

    public static $correctionsLibelles = [
        self::CORRECTION_AUTORISEE_OBLIGATOIRE => "Obligatoire",
        self::CORRECTION_AUTORISEE_FACULTATIVE => "Facultative",
    ];
    public static $correctionsLibellesPluriels = [
        self::CORRECTION_AUTORISEE_OBLIGATOIRE => "Obligatoires",
        self::CORRECTION_AUTORISEE_FACULTATIVE => "Facultatives",
    ];

    const CORRECTION_OBLIGATOIRE_INTERVAL = 'P2M';
    const CORRECTION_FACULTATIVE_INTERVAL = 'P3M';

    const CORRECTION_AUTORISEE_FORCAGE_NON = null; // pas de forçage
    const CORRECTION_AUTORISEE_FORCAGE_AUCUNE = 'aucune'; // aucune correction autorisée
    const CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE = self::CORRECTION_AUTORISEE_OBLIGATOIRE; // corrections obligatoires autorisées
    const CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE = self::CORRECTION_AUTORISEE_FACULTATIVE; // corrections facultatives autorisées

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
    private $correctionAutorisee;

    /**
     * @var string
     */
    private $correctionAutoriseeForcee;

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
     * @return DateTime
     */
    public function getDateSoutenance()
    {
        return $this->dateSoutenance;
    }

    /**
     * @return string
     */
    public function getDateSoutenanceToString()
    {
        return Util::formattedDate($this->getDateSoutenance());
    }

    /**
     * @param DateTime $dateSoutenance
     * @return self
     */
    public function setDateSoutenance(DateTime $dateSoutenance = null)
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
     * Indique si le témoin indique que des corrections sont autorisées.
     * NB: Par défaut le forçage du témoin est pris en compte
     *
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return bool
     */
    public function isCorrectionAutorisee($prendreEnCompteLeForcage = true)
    {
        return (bool) $this->getCorrectionAutorisee($prendreEnCompteLeForcage);
    }

    /**
     * Indique si le témoin de corrections autorisées fait l'objet d'un forçage.
     *
     * @return bool
     */
    public function isCorrectionAutoriseeForcee()
    {
        return $this->getCorrectionAutoriseeForcee() !== null;
    }

    /**
     * Retourne la valeur du témoin de corrections autorisées.
     * NB: Par défaut le forçage du témoin est pris en compte.
     *
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return string|null 'facultative' ou 'obligatoire' ou null
     *
     * @see These::getCorrectionAutoriseeForcee()
     */
    public function getCorrectionAutorisee($prendreEnCompteLeForcage = true)
    {
        if ($prendreEnCompteLeForcage === false) {
            return $this->correctionAutorisee;
        }

        if ($this->getCorrectionAutoriseeForcee() === self::CORRECTION_AUTORISEE_FORCAGE_AUCUNE) {
            // si le forçage est à 'aucune', alors aucune correction autorisée!
            return null;
        }

        return $this->getCorrectionAutoriseeForcee() ?: $this->correctionAutorisee;
    }

    /**
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return bool
     */
    public function getCorrectionAutoriseeEstFacultative($prendreEnCompteLeForcage = true)
    {
        return in_array($this->getCorrectionAutorisee($prendreEnCompteLeForcage), [
            self::CORRECTION_AUTORISEE_FACULTATIVE
        ]);
    }

    /**
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return bool
     */
    public function getCorrectionAutoriseeEstObligatoire($prendreEnCompteLeForcage = true)
    {
        return in_array($this->getCorrectionAutorisee($prendreEnCompteLeForcage), [
            self::CORRECTION_AUTORISEE_OBLIGATOIRE
        ]);
    }

    /**
     * @param bool $plural
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return string
     */
    public function getCorrectionAutoriseeToString($plural = false, $prendreEnCompteLeForcage = true)
    {
        $correctionAutorisee = $this->getCorrectionAutorisee($prendreEnCompteLeForcage);

        return $plural ?
            self::$correctionsLibellesPluriels[$correctionAutorisee] :
            self::$correctionsLibelles[$correctionAutorisee];
    }

    /**
     * Change la valeur du témoin de corrections autorisées importé.
     * NB: cette méthode ne devrait pas être utilisée, sauf pour les tests unitaires.
     *
     * @param string|null $correctionAutorisee
     * @return These
     */
    public function setCorrectionAutorisee(string $correctionAutorisee = null)
    {
        Assertion::inArray($correctionAutorisee, [
            null,
            self::CORRECTION_AUTORISEE_FACULTATIVE,
            self::CORRECTION_AUTORISEE_OBLIGATOIRE,
        ]);

        $this->correctionAutorisee = $correctionAutorisee;

        return $this;
    }

    /**
     * Retourne la valeur du forçage du témoin de corrections autorisées.
     *
     * @return string
     */
    public function getCorrectionAutoriseeForcee()
    {
        return $this->correctionAutoriseeForcee;
    }

    /**
     * Modifie la valeur du forçage du témoin de corrections autorisées.
     *
     * @param string|null $correctionAutoriseeForcee
     * @return These
     */
    public function setCorrectionAutoriseeForcee(string $correctionAutoriseeForcee = null)
    {
        Assertion::inArray($correctionAutoriseeForcee, [
            self::CORRECTION_AUTORISEE_FORCAGE_NON,
            self::CORRECTION_AUTORISEE_FORCAGE_AUCUNE,
            self::CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE,
            self::CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE,
        ]);

        $this->correctionAutoriseeForcee = $correctionAutoriseeForcee;

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
     * @return EcoleDoctorale
     */
    public function getEcoleDoctorale()
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @param EcoleDoctorale $ecoleDoctorale
     * @return self
     */
    public function setEcoleDoctorale($ecoleDoctorale)
    {
        $this->ecoleDoctorale = $ecoleDoctorale;

        return $this;
    }

    /**
     * @return UniteRecherche
     */
    public function getUniteRecherche()
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
     * @param bool $historisee
     * @return Collection
     */
    public function getDiffusions($historisee = false)
    {
        $diffusions = $this->miseEnLignes;

        $diffusions = $diffusions->filter(function(Diffusion $d) use ($historisee) {
            return $historisee === null || !$historisee === $d->estNonHistorise();
        });

        return $diffusions;
    }

    /**
     * Retourne l'éventuelle Diffusion de cette These.
     *
     * @param bool $historisee
     * @return Diffusion|null
     */
    public function getDiffusion($historisee = false)
    {
        return $this->getDiffusions($historisee)->first() ?: null;
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
     * @param string $code
     * @return Collection
     */
    public function getActeursByRoleCode($code)
    {
        $filter = function(Acteur $a) use ($code) {
            return $a->getRole()->getCode() === $code;
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
    public function hasActeurWithRole(Individu $individu, $role)
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
     * @param RdvBu $rdvBu
     * @return $this
     */
    public function addRdvBu(RdvBu $rdvBu)
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
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     */
    public function setEtablissementId($etablissement)
    {
        $this->etablissement = $etablissement;
    }

    /**
     * @return ArrayCollection
     */
    public function getAnneesUnivInscription()
    {
        return $this->anneesUnivInscription;
    }

    /**
     * Retourne les années universitaires d'inscription séparées par une virgule.
     *
     * @param string $glue Séparateur, ex: ' - '
     * @return string Ex: "2015/2016, 2016/2017, 2017/2018"
     */
    public function getAnneesUnivInscriptionToString($glue = ', ')
    {
        return implode($glue, array_map(function(TheseAnneeUniv $tau) {
            return $tau->getAnneeUnivToString();
        }, $this->anneesUnivInscription->toArray()));
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
     * @return ArrayCollection
     */
    public function getFinancements()
    {
        return $this->financements;
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
    public function getDirecteursTheseEmails(array &$individusSansMail = [])
    {
        $emails = [];
        $directeurs = $this->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);

        /** @var Acteur $acteur */
        foreach ($directeurs as $acteur) {
            $email = $acteur->getIndividu()->getEmail();
            $name = (string) $acteur->getIndividu();
            if (! $email) {
                $individusSansMail[$name] = $acteur->getIndividu();
            } else {
                $emails[$email] = $name;
            }
        }

        return $emails;
    }

    /**
     * @return DateTime|null
     */
    public function getDateNotificationDepotVersionCorrigeeAttendu()
    {
        switch ($this->getCorrectionAutorisee()) {
            case self::CORRECTION_AUTORISEE_OBLIGATOIRE:
                $dateButoir = $this->getDateButoirDepotVersionCorrigee();
                if ($dateButoir !== null) {
                    return $dateButoir->sub(new \DateInterval('P1M')); // date butoir - 1 mois
                } else {
                    return null;
                }
            case self::CORRECTION_AUTORISEE_FACULTATIVE:
                return null;
            default:
                return null;
        }
    }

    /**
     * @param DateTime $dateDerniereNotif
     * @return DateTime|null
     */
    public function getDateProchaineNotificationDepotVersionCorrigeeAttendu(DateTime $dateDerniereNotif = null)
    {
        $now = (new DateTime())->setTime(0, 0, 0);

        // La 1ere notification se fait immédiatement
        if ($dateDerniereNotif === null) {
            return $now;
        }

        $dateButoir = $this->getDateButoirDepotVersionCorrigee();

        switch ($this->getCorrectionAutorisee()) {
            case self::CORRECTION_AUTORISEE_OBLIGATOIRE:
                if ($dateButoir !== null) {
                    $dateProchaineNotif = $dateButoir->sub(new \DateInterval('P1M')); // Date butoir - 1 mois
                }
                else {
                    $dateProchaineNotif = null;
                }
                break;
            case self::CORRECTION_AUTORISEE_FACULTATIVE:
                $dateProchaineNotif = null;
                break;
            default:
                $dateProchaineNotif = null;
                break;
        }

        // Pas de date de prochaine notification !
        if ($dateProchaineNotif === null) {
            return null;
        }

        $dateProchaineNotif->setTime(0, 0, 0);

        // La date de prochaine notif est passée: pas de notif.
        if ($dateProchaineNotif < $now) {
            return null;
        }
        // La date de prochaine notif égale la date de dernière notif: les notifications sont terminées.
        if ($dateProchaineNotif == $dateDerniereNotif) {
            return null;
        }

        return $dateProchaineNotif;
    }

    /**
     * @return DateTime|null
     */
    public function getDateButoirDepotVersionCorrigee()
    {
        if ($this->getDateSoutenance() === null) {
            return null;
        }
        if ($this->getCorrectionAutorisee() === null) {
            return null;
        }

        $intervalSpec = $this->getDelaiDepotVersionCorrigeeInterval();

        $date = clone $this->getDateSoutenance();
        $date
            ->add(new \DateInterval($intervalSpec))
            ->setTime(0, 0, 0);

        return $date;
    }

    /**
     * @return bool
     */
    public function getDateButoirDepotVersionCorrigeeDepassee()
    {
        $dateButoir = $this->getDateButoirDepotVersionCorrigee();

        if ($dateButoir === null) {
            return false;
        }

        $today = new \DateTime('today');

        return $today > $dateButoir;
    }

    public function getDelaiDepotVersionCorrigeeInterval()
    {
        switch ($val = $this->getCorrectionAutorisee()) {
            case self::CORRECTION_AUTORISEE_OBLIGATOIRE:
                return static::CORRECTION_OBLIGATOIRE_INTERVAL; // + 3 mois
            case self::CORRECTION_AUTORISEE_FACULTATIVE:
                return static::CORRECTION_FACULTATIVE_INTERVAL; // + 2 mois
            default:
                throw new RuntimeException("Valeur de correction attendue non prévue: " . $val);
        }
    }

    public function getDelaiDepotVersionCorrigeeToString()
    {
        switch ($spec = $this->getDelaiDepotVersionCorrigeeInterval()) {
            case self::CORRECTION_OBLIGATOIRE_INTERVAL:
                return '3 mois';
            case self::CORRECTION_FACULTATIVE_INTERVAL:
                return '2 mois';
            default:
                throw new RuntimeException("Interval rencontré non prévu: " . $spec);
        }
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
            if ($fichier->getFichier()->getNature()->getCode() === NatureFichier::CODE_FICHIER_NON_PDF) return true;
        }
        return false;
    }

    public function hasMemoire()
    {
        foreach ($this->fichierTheses as $fichier) {
            if ($fichier->getFichier()->getNature()->getCode() === NatureFichier::CODE_THESE_PDF) return true;
        }
        return false;
    }

    public function hasVersionInitiale() {
        /** @var FichierThese $fichier */
        foreach ($this->fichierTheses as $fichier) {
            if ($fichier->getFichier()->getHistoDestruction() === null
                && $fichier->getFichier()->getNature()->getCode() === NatureFichier::CODE_THESE_PDF
                && $fichier->getFichier()->getVersion()->getCode() === VersionFichier::CODE_ORIG)
                    return $fichier;
        }
        return null;
    }

    public function hasVersionCorrigee() {
        /** @var FichierThese $fichier */
        foreach ($this->fichierTheses as $fichier) {
            if ($fichier->getFichier()->getHistoDestruction() === null
                && $fichier->getFichier()->getNature()->getCode() === NatureFichier::CODE_THESE_PDF
                && $fichier->getFichier()->getVersion()->getCode() === VersionFichier::CODE_ORIG_CORR)
                    return $fichier;
        }
        return null;
    }
}
