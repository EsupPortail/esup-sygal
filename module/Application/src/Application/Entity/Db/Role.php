<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenAuth\Entity\Db\AbstractRole;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * An entity that represents a role.
 */
class Role extends AbstractRole
{
    use SourceAwareTrait;
    use HistoriqueAwareTrait;

    // Memento: (&(eduPersonAffiliation=member)(eduPersonAffiliation=student)(eduPersonAffiliation=researcher))

    const CODE_DOCTORANT = 'DOCTORANT';
    const CODE_ADMIN = 'ADMIN';
    const CODE_BU = 'BU';
    const CODE_BDD = 'BDD';

    const CODE_DIRECTEUR_THESE = 'D';
    const CODE_MEMBRE_JURY = 'M';
    const CODE_PRESIDENT_JURY = 'P';
    const CODE_RAPPORTEUR_JURY = 'R';
    const CODE_CO_ENCADRANT = 'B';

    // @todo NB: maintenant il y le code étab concaténé au "role_id"
    const ROLE_ID_DOCTORANT = "Doctorant";
    const ROLE_ID_BUREAU_DES_DOCTORATS = "Bureau des doctorats";
    const ROLE_ID_BIBLIO_UNIV = "Bibliothèque universitaire";

    const ROLE_ID_ECOLE_DOCT = "École doctorale";
    const ROLE_ID_UNITE_RECH = "Unité de recherche";

    static public $ordreSourcesCodes = [
        self::CODE_DIRECTEUR_THESE => 'aa',
        self::CODE_CO_ENCADRANT    => 'ab',
        self::CODE_PRESIDENT_JURY  => 'b',
        self::CODE_MEMBRE_JURY     => 'c',
        self::CODE_RAPPORTEUR_JURY => 'd',
    ];

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * @var string Code unique *au sein d'un établissement*.
     */
    protected $code;

    /**
     * @var string
     */
    protected $libelle;

    /**
     * @return bool
     */
    public function estRoleDoctorant()
    {
        return $this->getCode() === self::CODE_DOCTORANT;
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
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
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
     * @var bool
     */
    private $attributionAutomatique = false;

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
}