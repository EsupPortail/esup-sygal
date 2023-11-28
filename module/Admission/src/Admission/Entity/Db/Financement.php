<?php
namespace Admission\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Financement implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var bool|null
     */
    private $contratDoctoral;

    /**
     * @var string|null
     */
    private $employeurContrat;

    /**
     * @var string|null
     */
    private $detailContratDoctoral;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * @var Collection
     */
    private $verificationFinancement;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->verificationFinancement = new ArrayCollection();
    }

    /**
     * Set contratDoctoral.
     *
     * @param bool|null $contratDoctoral
     *
     * @return Financement
     */
    public function setContratDoctoral($contratDoctoral = null)
    {
        $this->contratDoctoral = $contratDoctoral;

        return $this;
    }

    /**
     * Get contratDoctoral.
     *
     * @return bool|null
     */
    public function getContratDoctoral()
    {
        return $this->contratDoctoral;
    }

    /**
     * Set employeurContrat.
     *
     * @param string|null $employeurContrat
     *
     * @return Financement
     */
    public function setEmployeurContrat($employeurContrat = null)
    {
        $this->employeurContrat = $employeurContrat;

        return $this;
    }

    /**
     * Get employeurContrat.
     *
     * @return string|null
     */
    public function getEmployeurContrat()
    {
        return $this->employeurContrat;
    }

    /**
     * Set detailContratDoctoral.
     *
     * @param string|null $detailContratDoctoral
     *
     * @return Financement
     */
    public function setDetailContratDoctoral($detailContratDoctoral = null)
    {
        $this->detailContratDoctoral = $detailContratDoctoral;

        return $this;
    }

    /**
     * Get detailContratDoctoral.
     *
     * @return string|null
     */
    public function getDetailContratDoctoral()
    {
        return $this->detailContratDoctoral;
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
     * @return Financement
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

    /**
     * Get verificationFinancement.
     *
     * @return Collection
     */
    public function getVerificationFinancement(): Collection
    {
        return $this->verificationFinancement;
    }

    /**
     * Add VerificationFinancement.
     */
    public function addVerificationFinancement(Collection $verificationFinancements)
    {
//        foreach ($verificationFinancements as $vI) {
//            if (!$this->verificationFinancement->contains($vI)) {
//                $this->verificationFinancement->add($vI);
//            }
//        }

        return $this;
    }

    /**
     * Remove VerificationFinancement.
     */
    public function removeVerificationFinancement(Collection $verificationFinancements)
    {
        foreach ($verificationFinancements as $vI) {
            $this->verificationFinancement->removeElement($vI);
        }
    }
}
