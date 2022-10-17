<?php

namespace Structure\Entity\Db;

use Application\Entity\Db\Role;
use Application\Search\Filter\SearchFilterValueInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Etablissement
 */
class Etablissement
    implements StructureConcreteInterface, HistoriqueAwareInterface, SourceAwareInterface, SearchFilterValueInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use StructureAwareTrait;

    const SOURCE_CODE_ETABLISSEMENT_INCONNU = 'ETAB_INCONNU';

    const CODE_TOUT_ETABLISSEMENT_CONFONDU = 'Tous';

    protected $id;
    protected $domaine;
    protected $theses;
    protected $doctorants;
    protected $roles;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var bool
     */
    protected $estMembre = false;

    /**
     * @var bool
     */
    protected $estAssocie = false;

    /**
     * @var bool
     */
    protected $estInscription = false;

    /**
     * @var bool
     */
    protected $estComue = false;

    /**
     * Etablissement constructor.
     */
    public function __construct()
    {
        $this->structure = new Structure();
    }

    /**
     * Etablissement prettyPrint
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
     * Retourne le code de cet établissement ou null, ex: '0761904GE' pour l'Université de Rouen.
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
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
     * @return string
     */
    public function getDomaine()
    {
        return $this->domaine;
    }

    /**
     * @param string $domaine
     */
    public function setDomaine($domaine)
    {
        $this->domaine = $domaine;
    }

    /**
     * @return bool
     */
    public function estMembre()
    {
        return $this->estMembre;
    }

    /**
     * @param bool $estMembre
     * @return Etablissement
     */
    public function setEstMembre($estMembre)
    {
        $this->estMembre = $estMembre;

        return $this;
    }

    /**
     * @return bool
     */
    public function estAssocie()
    {
        return $this->estAssocie;
    }

    /**
     * @param bool $estAssocie
     * @return Etablissement
     */
    public function setEstAssocie($estAssocie)
    {
        $this->estAssocie = $estAssocie;

        return $this;
    }

    /**
     * @return bool
     */
    public function estInscription()
    {
        return $this->estInscription;
    }

    /**
     * @param bool $estInscription
     * @return Etablissement
     */
    public function setEstInscription($estInscription)
    {
        $this->estInscription = $estInscription;
        return $this;
    }

    /**
     * @return bool
     */
    public function estComue()
    {
        return $this->estComue;
    }

    /**
     * @param bool $estComue
     * @return Etablissement
     */
    public function setEstComue($estComue)
    {
        $this->estComue = $estComue;

        return $this;
    }

    /**
     * Retourne l'éventuel établissement substituant celui-ci.
     *
     * ATTENTION : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.etablissement'.
     *
     * @return \Structure\Entity\Db\Etablissement|null
     */
    public function getEtablissementSubstituant(): ?Etablissement
    {
        if ($substit = $this->structure->getStructureSubstituante()) {
            return $substit->getEtablissement();
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTheses()
    {
        return $this->theses;
    }

    /**
     * @return mixed
     */
    public function getDoctorants()
    {
        return $this->doctorants;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Teste si cet établissement est le pseudo-établissement "Tout établissement confondu".
     *
     * @return bool
     */
    public function estToutEtablissementConfondu()
    {
        return $this->structure->getCode() === self::CODE_TOUT_ETABLISSEMENT_CONFONDU;
    }

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        $label = ($this->code ?: $this->structure->getSigle()) ?: $this->structure->getLibelle();
        if ($this->structure->estFermee()) {
            $label .= "&nbsp; FERMÉ";
        }

        return ['value' => $this->getSourceCode(), 'label' => $label];
    }
}