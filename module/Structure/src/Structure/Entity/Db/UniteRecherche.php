<?php

namespace Structure\Entity\Db;

use Application\Entity\Db\DomaineScientifique;
use Application\Search\Filter\SearchFilterValueInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * UniteRecherche
 */
class UniteRecherche
    implements StructureConcreteInterface, HistoriqueAwareInterface, SourceAwareInterface, ResourceInterface, SearchFilterValueInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use StructureAwareTrait;

    /**
     * @var string
     */
    protected $etablissementsSupport;

    /**
     * @var string
     */
    protected $autresEtablissements;

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

    /** @var These[] */
    private $theses;

    public function __construct()
    {
        $this->structure = new Structure();
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * UniteRecherche prettyPrint
     * @return string
     */
    public function __toString() {
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
     * @return string
     */
    public function getEtablissementsSupport()
    {
        return $this->etablissementsSupport;
    }

    /**
     * @param string $etablissementsSupport
     * @return UniteRecherche
     */
    public function setEtablissementsSupport($etablissementsSupport)
    {
        $this->etablissementsSupport = $etablissementsSupport;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutresEtablissements()
    {
        return $this->autresEtablissements;
    }

    /**
     * @param string $autresEtablissements
     * @return UniteRecherche
     */
    public function setAutresEtablissements($autresEtablissements)
    {
        $this->autresEtablissements = $autresEtablissements;

        return $this;
    }

    /**
     * Retourne l'éventuelle unité de recherche substituant celle-ci.
     *
     * ATTENTION : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.uniteRecherche'.
     *
     * @return \Structure\Entity\Db\UniteRecherche|null
     */
    public function getUniteRechercheSubstituante(): ?UniteRecherche
    {
        if ($substit = $this->structure->getStructureSubstituante()) {
            return $substit->getUniteRecherche();
        }

        return null;
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
     * @return These[]
     */
    public function getTheses()
    {
        return $this->theses;
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