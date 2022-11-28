<?php

namespace Structure\Entity\Db;

use Application\Search\Filter\SearchFilterValueInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * EcoleDoctorale
 */
class EcoleDoctorale
    implements StructureConcreteInterface, HistoriqueAwareInterface, SourceAwareInterface, ResourceInterface, SearchFilterValueInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use StructureAwareTrait;

    const CODE_TOUTE_ECOLE_DOCTORALE_CONFONDUE = 'TOUTE_ED';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var string
     */
    protected $offreThese;
    /**
     * EcoleDoctorale constructor.
     */
    public function __construct()
    {
        $this->structure = new Structure();
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'EcoleDoctorale';
    }

    /**
     * EcoleDoctorale prettyPrint
     * @return string
     */
    public function __toString()
    {
        return $this->structure->getLibelle();
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
    public function getSourceCode(): ?string
    {
        return $this->sourceCode;
    }

    /**
     * Retourne l'éventuelle école doctorale substituant celle-ci.
     *
     * ATTENTION : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.ecoleDoctorale'.
     *
     * @return \Structure\Entity\Db\EcoleDoctorale|null
     */
    public function getEcoleDoctoraleSubstituante(): ?EcoleDoctorale
    {
        if ($substit = $this->structure->getStructureSubstituante()) {
            return $substit->getEcoleDoctorale();
        }

        return null;
    }

    /**
     * Teste si cette école doctorale est la pseudo-école doctorale "Toute école doctorale confondue".
     *
     * @return bool
     */
    public function estTouteEcoleDoctoraleConfondue()
    {
        return $this->structure->getCode() === self::CODE_TOUTE_ECOLE_DOCTORALE_CONFONDUE;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     * @return EcoleDoctorale
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return string
     */
    public function getOffreThese()
    {
        return $this->offreThese;
    }

    /**
     * @param string $offreThese
     * @return EcoleDoctorale
     */
    public function setOffreThese($offreThese)
    {
        $this->offreThese = $offreThese;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        $estFermee = $this->structure->estFermee();

        $subtext = $this->structure->getLibelle();
        if ($estFermee) {
            $subtext .= " - FERMÉE";
        }

        return [
            'value' => $this->getSourceCode(),
            'label' => $this->structure->getSigle(),
            'subtext' => $subtext,
            'class' => $estFermee ? 'fermee' : '',
        ];
    }
}