<?php

namespace These\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;

/**
 * VTheseAnneeUnivFirst
 */
class VTheseAnneeUnivFirst implements HistoriqueAwareInterface
{
    use TheseAnneeUnivTrait;

    /**
     * Set anneeUniv.
     *
     * @param int $anneeUniv
     *
     * @return VTheseAnneeUnivFirst
     */
    public function setAnneeUniv($anneeUniv)
    {
        $this->anneeUniv = $anneeUniv;

        return $this;
    }

    /**
     * Set sourceCode.
     *
     * @param string|null $sourceCode
     *
     * @return VTheseAnneeUnivFirst
     */
    public function setSourceCode($sourceCode = null)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return VTheseAnneeUnivFirst
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set these.
     *
     * @param \These\Entity\Db\These|null $these
     *
     * @return VTheseAnneeUnivFirst
     */
    public function setThese(\These\Entity\Db\These $these = null)
    {
        $this->these = $these;

        return $this;
    }
}
