<?php
namespace Admission\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Transmission implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var string|null
     */
    private $codeVoeu;

    /**
     * @var string|null
     */
    private $codePeriode;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * Set codeVoeu.
     *
     * @param string|null $codeVoeu
     *
     * @return Transmission
     */
    public function setCodeVoeu($codeVoeu = null)
    {
        $this->codeVoeu = $codeVoeu;

        return $this;
    }

    /**
     * Get codeVoeu.
     *
     * @return string|null
     */
    public function getCodeVoeu()
    {
        return $this->codeVoeu;
    }

    /**
     * Set codePeriode.
     *
     * @param string|null $codePeriode
     *
     * @return Transmission
     */
    public function setCodePeriode($codePeriode = null)
    {
        $this->codePeriode = $codePeriode;

        return $this;
    }

    /**
     * Get codePeriode.
     *
     * @return string|null
     */
    public function getCodePeriode()
    {
        return $this->codePeriode;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return Transmission
     */
    public function setAdmission(Admission $admission = null)
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission.
     *
     * @return Admission|null
     */
    public function getAdmission()
    {
        return $this->admission;
    }
}
