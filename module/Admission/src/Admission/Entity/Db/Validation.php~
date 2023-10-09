<?php
namespace Admission\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Validation implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Admission\Entity\Db\Admission
     */
    private $admissionId;

    /**
     * @var \Application\Entity\Db\TypeValidation
     */
    private $typeValidationId;

    /**
     * @var \Application\Entity\Db\Utilisateur
     */
    private $individuId;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->admissionId = new ArrayCollection();
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
     * Set admissionId.
     *
     * @param \Admission\Entity\Db\Admission|null $admissionId
     *
     * @return Validation
     */
    public function setAdmissionId(\Admission\Entity\Db\Admission $admissionId = null)
    {
        $this->admissionId = $admissionId;

        return $this;
    }

    /**
     * Get admissionId.
     *
     * @return \Admission\Entity\Db\Admission|null
     */
    public function getAdmissionId()
    {
        return $this->admissionId;
    }

    /**
     * Set typeValidationId.
     *
     * @param \Application\Entity\Db\TypeValidation|null $typeValidationId
     *
     * @return Validation
     */
    public function setTypeValidationId(\Application\Entity\Db\TypeValidation $typeValidationId = null)
    {
        $this->typeValidationId = $typeValidationId;

        return $this;
    }

    /**
     * Get typeValidationId.
     *
     * @return \Application\Entity\Db\TypeValidation|null
     */
    public function getTypeValidationId()
    {
        return $this->typeValidationId;
    }

    /**
     * Set individuId.
     *
     * @param \Application\Entity\Db\Utilisateur|null $individuId
     *
     * @return Validation
     */
    public function setIndividuId(\Application\Entity\Db\Utilisateur $individuId = null)
    {
        $this->individuId = $individuId;

        return $this;
    }

    /**
     * Get individuId.
     *
     * @return \Application\Entity\Db\Utilisateur|null
     */
    public function getIndividuId()
    {
        return $this->individuId;
    }
}
