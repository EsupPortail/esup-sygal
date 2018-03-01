<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Util;
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
     * @var Structure
     */
    protected $structure;

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
     * @param Structure $structure
     * @return UniteRecherche
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

    public function getLogoContent()
    {
        if ($this->cheminLogo === null) {
            $image = Util::createImageWithText("Aucun logo pour l'UR|[".$this->getSourceCode()." - ".$this->getSigle()."]",200,200);
            return $image;
        }
        return file_get_contents(APPLICATION_DIR . $this->cheminLogo);

    }
}