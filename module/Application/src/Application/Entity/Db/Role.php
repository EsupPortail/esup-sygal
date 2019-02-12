<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenAuth\Entity\Db\AbstractRole;
use UnicaenImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * An entity that represents a role.
 */
class Role extends AbstractRole implements SourceAwareInterface, HistoriqueAwareInterface
{
    use SourceAwareTrait;
    use HistoriqueAwareTrait;

    // Memento: (&(eduPersonAffiliation=member)(eduPersonAffiliation=student)(eduPersonAffiliation=researcher))

    const CODE_DOCTORANT = 'DOCTORANT';
    const CODE_ADMIN = 'ADMIN';
    const CODE_ADMIN_TECH = 'ADMIN_TECH';
    const CODE_BU = 'BU';
    const CODE_BDD = 'BDD';

    const CODE_DIRECTEUR_THESE = 'D';
    const CODE_CODIRECTEUR_THESE = 'K';
    const CODE_MEMBRE_JURY = 'M';
    const CODE_PRESIDENT_JURY = 'P';
    const CODE_RAPPORTEUR_JURY = 'R';
    const CODE_CO_ENCADRANT = 'B';
    const CODE_ED = 'ED';
    const CODE_UR = 'UR';

    // @todo NB: maintenant il y le code étab concaténé au "role_id"
    const ROLE_ID_DOCTORANT = "Doctorant";
    const ROLE_ID_BUREAU_DES_DOCTORATS = "Bureau des doctorats";
    const ROLE_ID_BIBLIO_UNIV = "Bibliothèque universitaire";

    const ROLE_ID_ECOLE_DOCT = "École doctorale";
    const ROLE_ID_UNITE_RECH = "Unité de recherche";
    const LIBELLE_PRESIDENT = "Président du jury";

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var TypeStructure
     */
    protected $typeStructureDependant;

    /**
     * @var Structure
     */
    protected $structure;

    /**
     * @var string Code unique *au sein d'un établissement*.
     */
    protected $code;

    /**
     * @var string
     */
    protected $libelle;

    /**
     * @var bool
     */
    private $attributionAutomatique = false;

    /**
     * @var bool
     */
    private $theseDependant = false;

    /**
     * @var int
     */
    private $ordreAffichage;

    /** @var ArrayCollection */
    private $profils;

    public function __construct()
    {
        parent::__construct();
        $this->profils = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isDoctorant()
    {
        return $this->getCode() === self::CODE_DOCTORANT;
    }

    /**
     * @return bool
     */
    public function isDirecteurThese()
    {
        return $this->getCode() === self::CODE_DIRECTEUR_THESE;
    }

    /**
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @param Structure $structure
     * @return Role
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Role
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
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
     * @return Role
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
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
     * @return bool
     */
    public function getAttributionAutomatique()
    {
        return $this->attributionAutomatique;
    }

    /**
     * @param bool $attributionAutomatique
     * @return self
     */
    public function setAttributionAutomatique($attributionAutomatique = true)
    {
        $this->attributionAutomatique = $attributionAutomatique;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTheseDependant()
    {
        return $this->theseDependant;
    }

    /**
     * @param bool $theseDependant
     * @return self
     */
    public function setTheseDependant($theseDependant = true)
    {
        $this->theseDependant = $theseDependant;

        return $this;
    }

    /**
     * @return TypeStructure
     */
    public function getTypeStructureDependant()
    {
        return $this->typeStructureDependant;
    }

    /**
     * @param TypeStructure $typeStructureDependant
     * @return self
     */
    public function setTypeStructureDependant(TypeStructure $typeStructureDependant)
    {
        $this->typeStructureDependant = $typeStructureDependant;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEtablissementDependant()
    {
        return ($tsd = $this->getTypeStructureDependant()) && $tsd->isEtablissement();
    }

    /**
     * @return bool
     */
    public function isEcoleDoctoraleDependant()
    {
        return ($tsd = $this->getTypeStructureDependant()) && $tsd->isEcoleDoctorale();
    }

    /**
     * @return bool
     */
    public function isUniteRechercheDependant()
    {
        return ($tsd = $this->getTypeStructureDependant()) && $tsd->isUniteRecherche();
    }

    /**
     * @return bool
     */
    public function isStructureDependant()
    {
        return $this->getTypeStructureDependant() !== null;
    }

    /**
     * Retourne une chaîne de caractères utilisée pour trier les rôles ;
     * l'astuce consiste à concaténer cette valeur aux autres critères de tri.
     *
     * @return string
     */
    public function getOrdreAffichage()
    {
        return $this->ordreAffichage;
    }

    /**
     * @param string $ordreAffichage
     * @return self
     */
    public function setOrdreAffichage($ordreAffichage)
    {
        $this->ordreAffichage = (string)$ordreAffichage;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str = $this->getLibelle();

        if ($this->getStructure() !== null) {
            $str .= " " . $this->getStructure()->getCode();
        }

        return $str;
    }

    /** return ArrayCollection */
    public function getProfils()
    {
        return $this->profils;
    }

    /**
     * @param Profil $profil
     * @return Role
     */
    public function addProfil($profil)
    {
        $this->profils->add($profil);
        return $this;
    }

    /**
     * @param Profil $profil
     * @return Role
     */
    public function removeProfil($profil)
    {
        $this->profils->removeElement($profil);
        return $this;
    }

    /**
     * @param Profil $profil
     * @return boolean
     */
    public function hasProfil($profil)
    {
        return $this->profils->contains($profil);
    }

}