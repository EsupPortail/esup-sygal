<?php

namespace Structure\Entity\Db;

use Application\Search\Filter\SearchFilterValueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * EcoleDoctorale
 */
class EcoleDoctorale implements
    StructureConcreteInterface,
    HistoriqueAwareInterface,
    SourceAwareInterface,
    ResourceInterface,
    SearchFilterValueInterface,
    SubstitutionAwareEntityInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use StructureAwareTrait;
    use SubstitutionAwareEntityTrait;

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
        $this->substitues = new ArrayCollection();
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

    public function __toString(): string
    {
        return implode(' - ', array_filter([
            $this->structure->getSigle(),
            $this->structure->getLibelle()
        ]));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->structure->getCode();
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

        return [
            'value' => $this->getSourceCode(),
            'label' => sprintf('%s - %s', $this->structure->getCode(), $this->structure->getLibelle()),
            'subtext' => $estFermee ? "Fermée" : null,
            'class' => $estFermee ? 'fermee' : '',
        ];
    }

    public function getTypeSubstitution(): string
    {
        return 'ecole_doct';
    }
}