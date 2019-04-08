<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;

/**
 * TheseAnneeUniv
 */
class TheseAnneeUniv implements HistoriqueAwareInterface
{
    use TheseAnneeUnivTrait;

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
     * @param int $anneeUniv
     * @return TheseAnneeUniv
     */
    public function setAnneeUniv($anneeUniv)
    {
        $this->anneeUniv = $anneeUniv;

        return $this;
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
