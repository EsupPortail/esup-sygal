<?php

namespace Application\Entity\Db;

use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;
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

    const CODE_ACCES_INTERNE = "I";
    const CODE_ACCES_EXTERNE = "E";

    const LIBELLE_ACCES_INTERNE = "Interne";
    const LIBELLE_ACCES_EXTERNE = "Externe";

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
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @var Pays
     */
    private $pays;

    /**
     * @return string
     */
    public function __toString()
    {
        $etab = $this->getTypeEtabTitreAcces();
        if ($this->getPays()) {
            $etab .= sprintf("%s, %s",
                $this->getCodeDeptTitreAcces(),
                $this->getPays()->getLibelle()
            );
        }

        return sprintf("%s (%s), %s (%s)",
            $this->getLibelleTitreAcces(),
            $this->getTitreAccesInterneExterneToString(),
            $this->getEtablissement(),
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
    public function getTitreAccesInterneExterneToString()
    {
        return $this->titreAccesInterneExterne ?
            [self::CODE_ACCES_INTERNE => self::LIBELLE_ACCES_INTERNE, self::CODE_ACCES_EXTERNE => self::LIBELLE_ACCES_EXTERNE][$this->titreAccesInterneExterne] :
            null;
    }

    /**
     * @return string
     */
    public function getTitreAccesInterneExterne()
    {
        return $this->titreAccesInterneExterne;
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
     * @deprecated
     */
    public function getLibelleEtabTitreAcces()
    {
        return $this->libelleEtabTitreAcces;
    }

    /**
     * @param string $libelleEtabTitreAcces
     * @deprecated
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
     * @deprecated
     */
    public function getCodePaysTitreAcces()
    {
        return $this->codePaysTitreAcces;
    }

    /**
     * @param string $codePaysTitreAcces
     * @deprecated
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

    /**
     * Set etablissement.
     *
     * @param Etablissement|null $etablissement
     *
     * @return TitreAcces
     */
    public function setEtablissement(Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Get etablissement.
     *
     * @return Etablissement|null
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set pays.
     *
     * @param Pays|null $pays
     *
     * @return TitreAcces
     */
    public function setPays(Pays $pays = null)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays.
     *
     * @return Pays|null
     */
    public function getPays()
    {
        return $this->pays;
    }
}
