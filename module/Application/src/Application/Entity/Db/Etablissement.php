<?php

namespace Application\Entity\Db;

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

    const SOURCE_CODE_ETABLISSEMENT_INCONNU = 'ETAB_INCONNU';

    const CODE_TOUT_ETABLISSEMENT_CONFONDU = 'Tous';

    protected $id;
    protected $domaine;
    protected $theses;
    protected $doctorants;
    protected $roles;

    /**
     * @var Structure
     */
    protected $structure;

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

    /** @var Fichier */

    protected $signatureConvocation;

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
     * @return string
     * @see StructureConcreteInterface
     */
    public function getCode()
    {
        return $this->getStructure()->getCode();
    }

    /**
     * @return string
     */
    public function generateUniqCode()
    {
        return uniqid();
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
     * @return string
     */
    public function getLibelle()
    {
        return $this->getStructure()->getLibelle();
    }

    /**
     * @param string $libelle
     */
    public function setLibelle($libelle)
    {
        $this->getStructure()->setLibelle($libelle);
    }

    /**
     * @return string
     */
    public function getSigle()
    {
        return $this->getStructure()->getSigle();
    }

    /**
     * @param string $sigle
     */
    public function setSigle($sigle)
    {
        $this->getStructure()->setSigle($sigle);
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
     * @return string
     */
    public function getCheminLogo()
    {
        return $this->getStructure()->getCheminLogo();
    }

    /**
     * @param string $cheminLogo
     */
    public function setCheminLogo($cheminLogo)
    {
        $this->getStructure()->setCheminLogo($cheminLogo);
    }

    /**
     * @return Fichier|null
     */
    public function getSignatureConvocation(): ?Fichier
    {
        return $this->signatureConvocation;
    }

    /**
     * @param Fichier|null $signatureConvocation
     * @return Etablissement
     */
    public function setSignatureConvocation(?Fichier $signatureConvocation): Etablissement
    {
        $this->signatureConvocation = $signatureConvocation;
        return $this;
    }

    /**
     * @param Structure $structure
     * @return self
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
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
        return $this->getCode() === self::CODE_TOUT_ETABLISSEMENT_CONFONDU;
    }

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        $label = $this->getCode();
        if ($this->getStructure()->isFerme()) {
            $label .= "&nbsp; FERMÉ";
        }

        return ['value' => $this->getSourceCode(), 'label' => $label];
    }
}