<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * TitreAcces
 */
class TitreAcces implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var string
     */
    protected $titreAccesInterneExterne;

    /**
     * @var string
     */
    protected $libelleTitreAcces;

    /**
     * @var string
     */
    protected $typeEtabTitreAcces;

    /**
     * @var string
     */
    protected $libelleEtabTitreAcces;

    /**
     * @var string
     */
    protected $codeDeptTitreAcces;

    /**
     * @var string
     */
    protected $codePaysTitreAcces;

    /**
     * @var These
     */
    private $these;

    /**
     * @return string
     */
    public function __toString()
    {
        $etab = $this->getTypeEtabTitreAcces();
        if ($this->getCodePaysTitreAcces()) {
            $etab .= sprintf("%s, %s",
                $this->getCodeDeptTitreAcces(),
                $this->getCodePaysTitreAcces()
            );
        }

        return sprintf("%s (%s), %s (%s)",
            $this->getLibelleTitreAcces(),
            $this->getTitreAccesInterneExterne(),
            $this->getLibelleEtabTitreAcces(),
            $etab
        );
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
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     */
    public function getTitreAccesInterneExterne()
    {
        return ['I' => 'Interne', 'E' => 'Externe'][$this->titreAccesInterneExterne];
    }

    /**
     * @param string $titreAccesInterneExterne
     */
    public function setTitreAccesInterneExterne($titreAccesInterneExterne)
    {
        $this->titreAccesInterneExterne = $titreAccesInterneExterne;
    }

    /**
     * @return string
     */
    public function getLibelleTitreAcces()
    {
        return $this->libelleTitreAcces;
    }

    /**
     * @param string $libelleTitreAcces
     */
    public function setLibelleTitreAcces($libelleTitreAcces)
    {
        $this->libelleTitreAcces = $libelleTitreAcces;
    }

    /**
     * @return string
     */
    public function getTypeEtabTitreAcces()
    {
        return $this->typeEtabTitreAcces;
    }

    /**
     * @param string $typeEtabTitreAcces
     */
    public function setTypeEtabTitreAcces($typeEtabTitreAcces)
    {
        $this->typeEtabTitreAcces = $typeEtabTitreAcces;
    }

    /**
     * @return string
     */
    public function getLibelleEtabTitreAcces()
    {
        return $this->libelleEtabTitreAcces;
    }

    /**
     * @param string $libelleEtabTitreAcces
     */
    public function setLibelleEtabTitreAcces($libelleEtabTitreAcces)
    {
        $this->libelleEtabTitreAcces = $libelleEtabTitreAcces;
    }

    /**
     * @return string
     */
    public function getCodeDeptTitreAcces()
    {
        return $this->codeDeptTitreAcces;
    }

    /**
     * @param string $codeDeptTitreAcces
     */
    public function setCodeDeptTitreAcces($codeDeptTitreAcces)
    {
        $this->codeDeptTitreAcces = $codeDeptTitreAcces;
    }

    /**
     * @return string
     */
    public function getCodePaysTitreAcces()
    {
        return $this->codePaysTitreAcces;
    }

    /**
     * @param string $codePaysTitreAcces
     */
    public function setCodePaysTitreAcces($codePaysTitreAcces)
    {
        $this->codePaysTitreAcces = $codePaysTitreAcces;
    }

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return self
     */
    public function setThese(These $these)
    {
        $this->these = $these;

        return $this;
    }
}
