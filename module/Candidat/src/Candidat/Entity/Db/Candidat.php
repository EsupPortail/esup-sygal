<?php

namespace Candidat\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Structure\Entity\Db\Etablissement;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * Candidat
 */
class Candidat implements
    HistoriqueAwareInterface,
    ResourceInterface,
    IndividuAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var integer
     */
    protected $id;


    /**
     * @var string
     */
    private $ine;

    /**
     * @var Individu
     */
    private $individu;


    /**
     * @var Etablissement|null
     */
    protected ?Etablissement $etablissement = null;

    /**
     * @var Collection
     */
    private $hdrs;

    /**
     * @var string
     */
    protected $sourceCode;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hdrs = new ArrayCollection();
    }

    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getIndividu()->__toString();
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get numeroEtudiant
     *
     * @return string
     */
    public function getNumeroEtudiant()
    {
        return $this->getIndividu()->getSupannId(); // todo: à remplacer par $this->>numeroEtudiant lorsqu'il sera importé.
    }

    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    public function setIndividu(?Individu $individu = null): self
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Retourne l'établissement lié.
     */
    public function getEtablissement(): Etablissement
    {
        $hdrs = $this->getHDRs();
        /** @var HDR $hdr */
        $hdr = (!empty($hdrs))?end($hdrs):null;
        return $hdr?->getEtablissement();
    }

    /**
     * @param Etablissement $etablissement
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
    }
    
    /**
     * @return \HDR\Entity\Db\HDR[]
     */
    public function getHDRs()
    {
        return $this->hdrs->toArray();
    }
    

    public function getIne(): string
    {
        return $this->ine;
    }

    public function setIne(string $ine): Candidat
    {
        $this->ine = $ine;
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
     * Retourne la dénomination du candidat (civilité+nom Patronymique+prénom)
     *
     * @return string
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDenominationPatronymique()
    {
        return $this->getIndividu()->getNomComplet(true, false, false, false, true);
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     * @see ResourceInterface
     */
    public function getResourceId()
    {
        return 'Candidat';
    }
}
