<?php

namespace Structure\Entity\Db;

use Application\Entity\Db\Role;
use Application\Entity\Db\Source;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Structure
 */
class Structure implements
    StructureInterface,
    HistoriqueAwareInterface,
    SourceAwareInterface,
    ResourceInterface,
    SubstitutionAwareEntityInterface
{
    use SourceAwareTrait;
    use HistoriqueAwareTrait;
    use SubstitutionAwareEntityTrait;

    const LOGO_ALLOWED_FILE_EXTENSIONS = ['bmp', 'png', 'jpg', 'jpeg'];

    /**
     * @var string $id
     * @var string $sigle
     * @var string $libelle
     * @var string $cheminLogo Nom du fichier (pas le chemin!)
     */
    private     $id;
    protected   $sigle;
    protected   $libelle;
    protected   $cheminLogo;
    protected   $estFermee = false;

    /**
     * @var string $adresse
     * @var string $telephone
     * @var string $fax
     * @var string $email
     * @var string $siteWeb
     * @var string $idRef
     */

    protected $adresse;
    protected $telephone;
    protected $fax;
    protected $email;
    protected $siteWeb;
    protected $idRef;
    protected $idHal;

    /**
     * @var string
     */
    protected $sourceCode;

    protected ?string $code = null;

    /**
     * @var TypeStructure
     */
    protected $typeStructure;

    /**
     * Relations one-to-many traités en comme des one-to-one (car un seul enregistrement cible non historisé)
     */
    protected Collection $etablissement;
    protected Collection $ecoleDoctorale;
    protected Collection $uniteRecherche;
    protected Collection $composanteEnseignement;

    /**
     * @var Role[] $roles
     */
    protected $roles;

    /** @var ArrayCollection StructureDocument */
    private $documents;

    /**
     * Instancie un Etablissement, une EcodeDoctorale ou une UniteRecherche à partir des données spécifiées.
     * NB: L'entité Structure de rattachement est également instanciée.
     *
     * @param StructureConcreteInterface $data
     * @param TypeStructure            $type
     * @param Source                   $source
     * @return Etablissement|EcoleDoctorale|UniteRecherche
     */
    public static function constructFromDataObject(StructureConcreteInterface $data, TypeStructure $type, Source $source)
    {
        // structure de rattachement
        $structureRattach = new Structure();
        $structureRattach->setTypeStructure($type);
        $structureRattach->setSource($source);
        $structureRattach->setCheminLogo($data->getStructure()->getCheminLogo());
        $structureRattach->setLibelle($data->getStructure()->getLibelle());
        $structureRattach->setSigle($data->getStructure()->getSigle());
        $structureRattach->setSourceCode($data->getSourceCode());
        $structureRattach->setCode($data->getStructure()->getCode());

        // structure concrète
        switch (true) {
            case $data instanceof Etablissement:
                $structure = new Etablissement();
                $structure->setDomaine($data->getDomaine());
                break;
            case $data instanceof EcoleDoctorale:
                $structure = new EcoleDoctorale();
                break;
            case $data instanceof UniteRecherche:
                $structure = new UniteRecherche();
                break;
            case $data instanceof ComposanteEnseignement:
                $structure = new ComposanteEnseignement();
                break;
            default:
                throw new LogicException("Type d'entité Structure spécifiée inattendu : " . get_class($data));
                break;
        }
        $structure->setSource($source);
        $structure->setSourceCode($data->getSourceCode());
        $structure->setStructure($structureRattach);

        return $structure;
    }

    public function __construct()
    {
        $this->etablissement = new ArrayCollection();
        $this->ecoleDoctorale = new ArrayCollection();
        $this->uniteRecherche = new ArrayCollection();
        $this->composanteEnseignement = new ArrayCollection();
        $this->substitues = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
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
     * Set code
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get Code
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    /**
     * @param string $sigle
     */
    public function setSigle($sigle)
    {
        $this->sigle = $sigle;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return Structure
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Retourne le Nom du fichier (pas le chemin!)
     *
     * @return string
     */
    public function getCheminLogo()
    {
        return $this->cheminLogo;
    }

    /**
     * SPécifie le Nom du fichier (pas le chemin!)
     *
     * @param string $cheminLogo
     */
    public function setCheminLogo($cheminLogo)
    {
        $this->cheminLogo = $cheminLogo;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return Role[]
     */
    public function getStructureDependantRoles()
    {
        $roles = [];
        foreach($this->roles as $role) {
            if ($role->isStructureDependant() && !$role->isTheseDependant()) $roles[] = $role;
        }
        return $roles;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * @return TypeStructure
     */
    public function getTypeStructure(): TypeStructure
    {
        return $this->typeStructure;
    }

    /**
     * @param TypeStructure $typeStructure
     * @return self
     */
    public function setTypeStructure(TypeStructure $typeStructure): self
    {
        $this->typeStructure = $typeStructure;

        return $this;
    }

    /**
     * Retourne la Structure "concrète" correspondant à cette Structure "abstraite".
     * Si toutes les structures concrètes pointant sur cette structure abstraite sont historisées, `null` est retourné.
     *
     * @return \Structure\Entity\Db\StructureConcreteInterface|null
     */
    public function getStructureConcrete(): ?StructureConcreteInterface
    {
        return match (true) {
            $this->typeStructure->isEtablissement() => $this->getEtablissement(),
            $this->typeStructure->isEcoleDoctorale() => $this->getEcoleDoctorale(),
            $this->typeStructure->isUniteRecherche() => $this->getUniteRecherche(),
            $this->typeStructure->isComposanteEnseignement() => $this->getComposanteEnseignement(),
            default => throw new InvalidArgumentException("Type de structure inattendu"),
        };
    }

    /**
     * Retourne le 1er Etablissement *non historisé* associé à cette Structure "abstraite",
     * tel que défini par la jointure Doctrine.
     *
     * @see getStructureConcrete()
     * @return \Structure\Entity\Db\Etablissement|null
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement->filter(fn(Etablissement $e) => $e->estNonHistorise())->first() ?: null;
    }

    /**
     * Retourne la 1ere EcoleDoctorale *non historisée* associée à cette Structure "abstraite",
     * telle que définie par la jointure Doctrine.
     *
     * @see getStructureConcrete()
     * @return \Structure\Entity\Db\EcoleDoctorale|null
     */
    public function getEcoleDoctorale(): ?EcoleDoctorale
    {
        return $this->ecoleDoctorale->filter(fn(EcoleDoctorale $ed) => $ed->estNonHistorise())->first() ?: null;
    }

    /**
     * Retourne l'éventuelle ComposanteEnseignement correspondant à cette Structure "abstraite",
     * telle que défini par la jointure Doctrine.
     *
     * @see getStructureConcrete()
     * @return \Structure\Entity\Db\ComposanteEnseignement|null
     */
    public function getComposanteEnseignement(): ?ComposanteEnseignement
    {
        return $this->composanteEnseignement;
    }

    /**
     * Retourne la 1ere UniteRecherche *non historisée* associée à cette Structure "abstraite",
     * telle que définie par la jointure Doctrine.
     *
     * @see getStructureConcrete()
     * @return \Structure\Entity\Db\UniteRecherche|null
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        return $this->uniteRecherche->filter(fn(UniteRecherche $ur) => $ur->estNonHistorise())->first() ?: null;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return 'structure';
    }

    /**
     * @return bool
     */
    public function estFermee(): bool
    {
        return $this->estFermee;
    }

    /**
     * @param boolean $estFermee
     */
    public function setEstFermee(bool $estFermee = true)
    {
        $this->estFermee = $estFermee;
    }

    /**
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @param string $adresse
     * @return Structure
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param string $telephone
     * @return Structure
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     * @return Structure
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Structure
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getSiteWeb()
    {
        return $this->siteWeb;
    }

    /**
     * @param string $siteWeb
     * @return Structure
     */
    public function setSiteWeb($siteWeb)
    {
        $this->siteWeb = $siteWeb;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdRef()
    {
        return $this->idRef;
    }

    /**
     * @param mixed $idRef
     */
    public function setIdRef($idRef)
    {
        $this->idRef = $idRef;
    }

    /**
     * @return string|null
     */
    public function getIdHal(): ?string
    {
        return $this->idHal;
    }

    /**
     * @param string|null $idHal
     */
    public function setIdHal(?string $idHal): void
    {
        $this->idHal = $idHal;
    }

    /**
     * @return \Structure\Entity\Db\StructureDocument[][]
     */
    public function getDocuments() : array
    {
        $array = [];
        /** @var StructureDocument $document */
        foreach ($this->documents as $document) {
            if ($document->estNonHistorise()) $array[$document->getNature()->getCode()][] = $document;
        }
        return $array;
    }

    public function getTypeSubstitution(): string
    {
        return 'structure';
    }
}