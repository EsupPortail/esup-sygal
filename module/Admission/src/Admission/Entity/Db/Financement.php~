<?php
namespace Admission\Entity\Db;

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
     * @var Collection
     */
    private $admissionId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->admissionId = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add admissionId.
     *
     * @param \Admission\Entity\Admission $admissionId
     *
     * @return Financement
     */
    public function addAdmissionId(\Admission\Entity\Admission $admissionId)
    {
        $this->admissionId[] = $admissionId;

        return $this;
    }

    /**
     * Remove admissionId.
     *
     * @param Admission $admissionId
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAdmissionId(Admission $admissionId)
    {
        return $this->admissionId->removeElement($admissionId);
    }

    /**
     * Get admissionId.
     *
     * @return Collection
     */
    public function getAdmissionId()
    {
        return $this->admissionId;
    }

    /**
     * Set admissionId.
     *
     * @param Admission|null $admissionId
     *
     * @return Financement
     */
    public function setAdmissionId(Admission $admissionId = null)
    {
        $this->admissionId = $admissionId;

        return $this;
    }
}
