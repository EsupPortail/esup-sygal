<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;

trait TheseAnneeUnivTrait
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
     * @var integer
     */
    protected $anneeUniv;

    /**
     * @var These
     */
    protected $these;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAnneeUnivToString();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return int
     */
    public function getAnneeUnivToString()
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
}
