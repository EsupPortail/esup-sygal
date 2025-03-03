<?php

namespace HDR\Entity\Db;

use Acteur\Entity\Db\AbstractActeur;
use Application\Entity\Db\Discipline;
use Application\Entity\Db\Role;
use Application\Entity\Db\VersionDiplome;
use Candidat\Entity\Db\Candidat;
use DateTime;
use Depot\Entity\Db\FichierHDR;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Horodatage\Entity\Interfaces\HasHorodatagesInterface;
use Horodatage\Entity\Traits\HasHorodatagesTrait;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\PropositionHDR;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use Acteur\Entity\Db\ActeurHDR;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationHDR;

class HDR implements HistoriqueAwareInterface, HasHorodatagesInterface, ResourceInterface {
    use HistoriqueAwareTrait;
    use HasHorodatagesTrait;
    use SourceAwareTrait;

    const RESOURCE_ID = "HDR";

    const ETAT_EN_COURS   = 'E';
    const ETAT_ABANDONNEE = 'A';
    const ETAT_SOUTENUE   = 'S';

    public static array $etatsLibelles = [
        self::ETAT_EN_COURS   => "En cours",
        self::ETAT_ABANDONNEE => "Abandonnée",
        self::ETAT_SOUTENUE   => "Soutenue",
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
     * @var ArrayCollection
     */
    private $propositionsHDR;
    private $id;

    /**
     * @var Candidat
     */
    private $candidat;

    /**
     * @var string
     */
    private string $etatHDR = self::ETAT_EN_COURS;

    private ?Discipline $discipline = null;
    private ?VersionDiplome $versionDiplome = null;

    /**
     * @var string
     */
    private $cnu;

    /**
     * @var EcoleDoctorale
     */
    private $ecoleDoctorale;

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;

    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @var DateTime
     */
    protected $dateFinConfidentialite;

    /**
     * @var DateTime|null
     */
    protected $dateAbandon;

    /**
     * @var ArrayCollection
     */
    private $acteurs;

    /**
     * @var ArrayCollection
     */
    private $validations;

    /**
     * @var Collection
     */
    private $fichierHDRs;

    /**
     * @var null|int
     */
    private $resultat = null;

    /**
     * @var string
     */
    protected $sourceCode;

    public function __construct()
    {
        $this->propositionsHDR = new ArrayCollection();
        $this->acteurs = new ArrayCollection();
        $this->validations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->id ?: "";
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCandidat()
    {
        return $this->candidat;
    }

    /**
     * @param Candidat $candidat
     */
    public function setCandidat(Candidat $candidat): void
    {
        $this->candidat = $candidat;
    }

    /**
     * @return EcoleDoctorale|null
     */
    public function getEcoleDoctorale(): ?EcoleDoctorale
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @param EcoleDoctorale $ecoleDoctorale
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale): void
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
    }

    /**
     * @return UniteRecherche|null
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        return $this->uniteRecherche;
    }

    /**
     * @param UniteRecherche $uniteRecherche
     */
    public function setUniteRecherche(UniteRecherche $uniteRecherche): void
    {
        $this->uniteRecherche = $uniteRecherche;
    }

    /**
     * @return Etablissement|null
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     */
    public function setEtablissement(Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    /**
     * Set discipline.
     *
     * @param Discipline|null $discipline
     *
     * @return HDR
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

    public function setVersionDiplome(?VersionDiplome $versionDiplome): static
    {
        $this->versionDiplome = $versionDiplome;

        return $this;
    }

    public function getVersionDiplome(): ?VersionDiplome
    {
        return $this->versionDiplome;
    }

    /**
     * @return string
     */
    public function getCnu(): ?string
    {
        return $this->cnu;
    }

    /**
     * @param string $cnu
     */
    public function setCnu(string $cnu): void
    {
        $this->cnu = $cnu;
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
     * @param DateTime|null $dateAbandon
     * @return HDR
     */
    public function setDateAbandon(DateTime $dateAbandon = null): HDR
    {
        $this->dateAbandon = $dateAbandon;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateAbandonToString(): string
    {
        return Util::formattedDate($this->getDateAbandon());
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
     */
    public function setSourceCode(string $sourceCode): void
    {
        $this->sourceCode = $sourceCode;
    }

    /**
     * @param bool $exceptHistorises
     * @return Collection
     */
    public function getActeurs($exceptHistorises = true)
    {
        if ($exceptHistorises) {
            return $this->acteurs->filter(function (ActeurHDR $a) {
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

        usort($acteurs, $callable ?: ActeurHDR::getComparisonFunction());

        return new ArrayCollection($acteurs);
    }

    /**
     * Retourne les acteurs de cette HDR dont le rôle est parmi ceux spécifiés
     *
     * @param string|string[] $code
     * @return Collection
     */
    public function getActeursByRoleCode($code): Collection
    {
        $codes = (array) $code;
        $filter = function(ActeurHDR $a) use ($codes) {
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
        $formatter = function(ActeurHDR $a) use ($includeRole) {
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
    public function hasActeurWithRole(Individu $individu, Role|string $role): bool
    {
        if ($role instanceof Role) {
            $role = $role->getCode();
        }

        $individus = $this->getActeursByRoleCode($role)->map(function(AbstractActeur $a) { return $a->getIndividu(); });

        return $individus->contains($individu);
    }

    /**
     * @param boolean $asIndividu
     * @return ActeurHDR[]|Individu[]
     */
    public function getEncadrements($asIndividu = false)
    {
        /** @var ActeurHDR[] $acteurs */
        $acteurs = [];

        $garants     = $this->getActeursByRoleCode(Role::CODE_HDR_GARANT);
        foreach ($garants as $garant) $acteurs[] = $garant;

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
     * @param ActeurHDR $acteur
     * @return $this
     */
    public function addActeur(ActeurHDR $acteur)
    {
        $this->acteurs->add($acteur);
        return $this;
    }

    /**
     * @param ActeurHDR $acteur
     * @return $this
     */
    public function removeActeur(ActeurHDR $acteur)
    {
        $this->acteurs->removeElement($acteur);
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
        $validations = $validations->filter(function(ValidationHDR $v) use ($type) {
            return $v->getValidation()->getTypeValidation()->getCode() === $type;
        });
        $validations = $validations->filter(function (ValidationHDR $v) use ($historisee) {
            return !$historisee === $v->estNonHistorise();
        });

        return $validations;
    }

    /**
     * Retourne l'éventuelle validation du type spécifié.
     */
    public function getValidation(string|TypeValidation $type, bool $historisee = false): ?ValidationHDR
    {
        return $this->getValidations($type, $historisee)->first() ?: null;
    }

    public function addValidation(ValidationHDR $validation): static
    {
        $this->validations->add($validation);

        return $this;
    }

    public function removeValidation(ValidationHDR $validation): static
    {
        $this->validations->removeElement($validation);

        return $this;
    }

    /**
     * @return PropositionHDR
     */
    public function getCurrentProposition(): PropositionHDR|null
    {
        $proposition = null;
        foreach ($this->getPropositionsHDR() as $p) {
            if ($p->estNonHistorise()) {
                $proposition = $p;
                break;
            }
        }
        return $proposition;
    }

    /**
     * @return Collection
     */
    public function getPropositionsHDR(): Collection
    {
        return $this->propositionsHDR;
    }

    /**
     * @param Collection $propositionsHDR
     */
    public function setPropositionsHDR(Collection $propositionsHDR): void
    {
        $this->propositionsHDR = $propositionsHDR;
    }

    /**
     * @return Collection
     */
    public function getFichierHDRs()
    {
        return $this->fichierHDRs;
    }

    /**
     * @param FichierHDR $fichierHDR
     * @return $this
     */
    public function addFichierHDR(FichierHDR $fichierHDR)
    {
        $this->fichierHDRs->add($fichierHDR);

        return $this;
    }

    /**
     * @param FichierHDR $fichierHDR
     * @return $this
     */
    public function removeFichierHDR(FichierHDR $fichierHDR)
    {
        $this->fichierHDRs->removeElement($fichierHDR);

        return $this;
    }

    /**
     * @param Fichier $fichier
     * @return $this
     */
    public function removeFichier(Fichier $fichier)
    {
        $fichierHDR = $this->fichierHDRs->filter(function(FichierHDR $ft) use ($fichier) {
            return $ft->getFichier() === $fichier;
        })->first();
        if (! $fichierHDR) {
            throw new RuntimeException("Le fichier à supprimer est introuvable parmi les fichiers de la HDR");
        }

        $this->removeFichierHDR($fichierHDR);

        return $this;
    }

    /**
     * @return string
     */
    public function getEtatHDR()
    {
        return $this->etatHDR;
    }

    /**
     * @param string $etatHDR
     * @return self
     */
    public function setEtatHDR($etatHDR)
    {
        $this->etatHDR = $etatHDR;

        return $this;
    }

    /**
     * @return string
     */
    public function getEtatHDRToString()
    {
        return self::$etatsLibelles[$this->etatHDR];
    }

    public function hasVersionInitiale() {

        /** @var FichierHDR $fichier */
        foreach ($this->fichierHDRs as $fichierHDR) {
            /** @var Fichier $fichier */
            $fichier = $fichierHDR->getFichier();
            $nature = $fichier->getNature()->getCode();
            $version = $fichier->getVersion()->getCode();
            if ($fichier->getHistoDestruction() === null
                && $nature === NatureFichier::CODE_THESE_PDF
                && $version === VersionFichier::CODE_ORIG)
                return $fichierHDR;
        }
        return null;
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
     * @param null|int $resultat
     * @return self
     */
    public function setResultat($resultat = null)
    {
        $this->resultat = $resultat;

        return $this;
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

    public function getApprenant(): Candidat
    {
        return $this->getCandidat();
    }

    /**
     * Retourne les mails du garant.
     *
     * @param Individu[] $individusSansMail Liste des individus sans mail, format: "Paul Hochon" => Individu
     * @return array
     */
    public function getGarantEmails(array &$individusSansMail = []): array
    {
        $emails = [];
        /** @var ActeurHDR[] $garants[] */
        $garants = $this->getActeursByRoleCode(Role::CODE_HDR_GARANT)->toArray();
        $emailExtractor = fn(Individu $i) => $i->getEmailPro() ?: $i->getEmailUtilisateur();

        /** @var ActeurHDR $garant */
        foreach ($garants as $garant) {
            $individu = $garant->getIndividu();
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
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }
}