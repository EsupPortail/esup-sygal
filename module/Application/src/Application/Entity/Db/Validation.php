<?php

namespace Application\Entity\Db;

use Application\Constants;
use DateTime;
use Individu\Entity\Db\Individu;
use These\Entity\Db\These;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Validation
 */
class Validation implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;

    const RESOURCE_ID_VALIDATION_ENSEIGNEMENT = 'VALIDATION_ENSEIGNEMENT';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \These\Entity\Db\These
     */
    private $these;

    /**
     * @var TypeValidation
     */
    private $typeValidation;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * Validation constructor.
     *
     * @param TypeValidation $type
     * @param These          $these
     * @param Individu       $individu
     */
    public function __construct(TypeValidation $type = null, These $these = null, Individu $individu = null)
    {
        $this->setTypeValidation($type);
        $this->setThese($these);
        $this->setIndividu($individu);
    }

    /**
     * Représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("Validation du %s par %s",
            $this->getHistoCreation()->format(Constants::DATETIME_FORMAT),
            $this->getHistoCreateur());
    }

    /**
     * Get histoModification
     */
    public function getHistoModification(): ?DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set intervenant
     *
     * @param These $these
     *
     * @return Validation
     */
    public function setThese(These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these
     *
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * Set typeValidation
     *
     * @param TypeValidation $typeValidation
     *
     * @return Validation
     */
    public function setTypeValidation(TypeValidation $typeValidation = null)
    {
        $this->typeValidation = $typeValidation;

        return $this;
    }

    /**
     * Get typeValidation
     *
     * @return TypeValidation
     */
    public function getTypeValidation()
    {
        return $this->typeValidation;
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return Validation
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;

        return $this;
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     * @see ResourceInterface
     */
    public function getResourceId()
    {
        return 'Validation';
    }
}
