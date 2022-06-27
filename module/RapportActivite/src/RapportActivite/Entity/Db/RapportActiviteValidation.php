<?php

namespace RapportActivite\Entity\Db;

use Application\Constants;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\TypeValidation;
use DateTime;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * RapportValidation
 */
class RapportActiviteValidation implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var RapportActivite
     */
    private $rapport;

    /**
     * @var \Application\Entity\Db\TypeValidation
     */
    private $typeValidation;

    /**
     * @var \Individu\Entity\Db\Individu
     *
     */
    private $individu;

    /**
     * RapportValidation constructor.
     *
     * @param TypeValidation|null $type
     * @param RapportActivite|null $rapport
     * @param Individu|null $individu
     */
    public function __construct(TypeValidation $type = null, RapportActivite $rapport = null, Individu $individu = null)
    {
        $this->setTypeValidation($type);
        $this->setRapport($rapport);
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
     *
     * @return DateTime
     */
    public function getHistoModification(): DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set intervenant
     *
     * @param RapportActivite|null $rapport
     * @return self
     */
    public function setRapport(RapportActivite $rapport = null): self
    {
        $this->rapport = $rapport;

        return $this;
    }

    /**
     * Get these
     *
     * @return RapportActivite
     */
    public function getRapport(): RapportActivite
    {
        return $this->rapport;
    }

    /**
     * Set typeValidation
     *
     * @param TypeValidation|null $typeValidation
     *
     * @return self
     */
    public function setTypeValidation(TypeValidation $typeValidation = null): RapportActiviteValidation
    {
        $this->typeValidation = $typeValidation;

        return $this;
    }

    /**
     * Get typeValidation
     *
     * @return TypeValidation
     */
    public function getTypeValidation(): TypeValidation
    {
        return $this->typeValidation;
    }

    /**
     * @return Individu
     */
    public function getIndividu(): Individu
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return self
     */
    public function setIndividu(Individu $individu): RapportActiviteValidation
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
    public function getResourceId(): string
    {
        return 'RapportValidation';
    }
}
