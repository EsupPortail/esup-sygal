<?php

namespace Application\Entity\Db;

use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * EcoleDoctorale
 */
class EcoleDoctorale implements StructureConcreteInterface, HistoriqueAwareInterface, SourceAwareInterface, ResourceInterface
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
    protected $sourceCode;

    /**
     * @var Structure
     */
    protected $structure;

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
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     * @deprecated mais encore nécessaire à cause de StructureConcreteInterface
     * @see StructureConcreteInterface
     */
    public function getCode() {
        return $this->structure->getCode();
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


}