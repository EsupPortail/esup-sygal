<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * TheseAnneeUniv
 */
class TheseAnneeUniv implements HistoriqueAwareInterface
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
     * @var integer
     */
    protected $anneeUniv;

    /**
     * @var These
     */
    private $these;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAnneeUniv1ereInscriptionToString();
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
     * @return int
     */
    public function getAnneeUniv()
    {
        return $this->anneeUniv;
    }

    /**
     * @param int $anneeUniv
     * @return TheseAnneeUniv
     */
    public function setAnneeUniv($anneeUniv)
    {
        $this->anneeUniv = $anneeUniv;

        return $this;
    }

    /**
     * @return int
     */
    public function getAnneeUniv1ereInscriptionToString()
    {
        return $this->anneeUniv . '/' . ($this->anneeUniv + 1);
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
