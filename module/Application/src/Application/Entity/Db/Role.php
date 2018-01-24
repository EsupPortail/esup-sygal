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

    const SOURCE_CODE_DIRECTEUR_THESE = 'D';
    const SOURCE_CODE_MEMBRE_JURY = 'M';
    const SOURCE_CODE_PRESIDENT_JURY = 'P';
    const SOURCE_CODE_RAPPORTEUR_JURY = 'R';
    const SOURCE_CODE_CO_ENCADRANT = 'B';

    const ROLE_ID_DOCTORANT = "Doctorant";
    const ROLE_ID_BUREAU_DES_DOCTORATS = "Bureau des doctorats";
    const ROLE_ID_BIBLIO_UNIV = "Bibliothèque universitaire";
    const ROLE_ID_ECOLE_DOCT = "École doctorale";
    const ROLE_ID_UNITE_RECH = "Unité de recherche";

    static public $ordreSourcesCodes = [
        self::SOURCE_CODE_DIRECTEUR_THESE => 'aa',
        self::SOURCE_CODE_CO_ENCADRANT    => 'ab',
        self::SOURCE_CODE_PRESIDENT_JURY  => 'b',
        self::SOURCE_CODE_MEMBRE_JURY     => 'c',
        self::SOURCE_CODE_RAPPORTEUR_JURY => 'd',
    ];

    /**
     * @var string
     */
    protected $sourceCode;

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