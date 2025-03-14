<?php

namespace Structure\Entity\Db;

use Application\Entity\Db\DomaineScientifique;
use Application\Search\Filter\SearchFilterValueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * UniteRecherche
 */
class UniteRecherche implements
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

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $domaines;

    /** @var string RNSR */
    protected $RNSR;

    /**
     * Convertit la collection d'entités spécifiée en un tableau d'options injectable dans un <select>.
     *
     * @param \Structure\Entity\Db\UniteRecherche[] $entities
     * @return string[] id => libelle
     */
    static public function toValueOptions(iterable $entities): array
    {
        $options = [];
        foreach ($entities as $entity) {
            $options[$entity->getId()] = (string) $entity;
        }

        return $options;
    }

    public function __construct()
    {
        $this->structure = new Structure();
        $this->domaines = new ArrayCollection();
        $this->substitues = new ArrayCollection();
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'UniteRecherche';
    }

    public function __toString(): string
    {
        $sigle = $this->structure->getSigle();

        return implode(' -- ', array_filter([
            $this->structure->getCode(),
            $this->structure->getLibelle() . ($sigle ? sprintf(" (%s)", $sigle) : null),
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
    public function getSourceCode(): ?string
    {
        return $this->sourceCode;
    }

    /**
     * @return DomaineScientifique[]
     */
    public function getDomaines()
    {
        return $this->domaines->toArray();
    }

    /**
     * @param DomaineScientifique $domaine
     * @return UniteRecherche
     */
    public function addDomaine($domaine)
    {
        $this->domaines[] = $domaine;
        return $this;
    }

    /**
     * @param UniteRecherche $unite
     * @return UniteRecherche
     */
    public function removeDomaine($domaine)
    {
        $this->domaines->removeElement($domaine);
        return $this;
    }

    /**
     * @return string
     */
    public function getRNSR()
    {
        return $this->RNSR;
    }

    /**
     * @param string $RNSR
     * @return UniteRecherche
     */
    public function setRNSR($RNSR)
    {
        $this->RNSR = $RNSR;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        $estFermee = $this->structure->estFermee();

        $libelle = $this->structure->getLibelle();
        $sigle = trim($this->structure->getSigle()) ?: '???';
        $code = $this->structure->getCode();

        $label = sprintf('%s - %s', $sigle, $libelle);
        if ($code) {
            $label .= sprintf(' (%s)', $code);
        }

        return [
            'value' => $this->getSourceCode(),
            'label' => $label,
            'subtext' => $estFermee ? "Fermée" : null,
            'class' => $estFermee ? 'fermee' : '',
        ];
    }

    public function getTypeSubstitution(): string
    {
        return 'unite_rech';
    }

    /** Pour macro ****************************************************************************************************/

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getSigle(): string
    {
        return $this->structure->getSigle() ? $this->structure->getSigle() : "";
    }
}