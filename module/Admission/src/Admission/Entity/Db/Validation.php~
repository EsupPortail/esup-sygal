<?php
namespace Admission\Entity\Db;

use Application\Entity\Db\Utilisateur;
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
     * @var \Application\Entity\Db\TypeValidation
     */
    private $typeValidationId;

    /**
     * @var Utilisateur
     */
    private $individuId;
    /**
     * @var Admission
     */
    private $admission;

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
     * @param Utilisateur|null $individuId
     *
     * @return Validation
     */
    public function setIndividuId(Utilisateur $individuId = null)
    {
        $this->individuId = $individuId;

        return $this;
    }

    /**
     * Get individuId.
     *
     * @return Utilisateur|null
     */
    public function getIndividuId()
    {
        return $this->individuId;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return Validation
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
