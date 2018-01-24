<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;


/**
 * UniteRecherche
 */
class UniteRecherche implements HistoriqueAwareInterface, SourceAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var string
     */
    protected $libelle;

    /**
     * @var string
     */
    protected $sigle;

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
     * @var Collection
     */
    protected $uniteRechercheIndividus;

    /**
     * UniteRecherche constructor.
     */
    public function __construct()
    {
        $this->uniteRechercheIndividus = new ArrayCollection();
    }


    public function __toString()
    {
        return $this->getLibelle();
    }
    
    /**
     * Set libelleCourt
     *
     * @param string $libelle
     * @return UniteRecherche
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelleCourt
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set libelleLong
     *
     * @param string $sigle
     * @return UniteRecherche
     */
    public function setSigle($sigle)
    {
        $this->sigle = $sigle;

        return $this;
    }

    /**
     * Get libelleLong
     *
     * @return string 
     */
    public function getSigle()
    {
        return $this->sigle;
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
     * @return Collection
     */
    public function getUniteRechercheIndividus()
    {
        return $this->uniteRechercheIndividus;
    }

    /**
     * @param UniteRechercheIndividu $edi
     * @return self
     */
    public function addUniteRechercheIndividu(UniteRechercheIndividu $edi)
    {
        $this->uniteRechercheIndividus->add($edi);

        return $this;
    }

    /**
     * @param UniteRechercheIndividu $edi
     * @return self
     */
    public function removeUniteRechercheIndividu(UniteRechercheIndividu $edi)
    {
        $this->uniteRechercheIndividus->removeElement($edi);

        return $this;
    }
}