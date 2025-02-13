<?php

namespace Validation\Entity\Db;

use Application\Constants;
use DateTime;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Validation implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var TypeValidation
     */
    private $typeValidation;

    public function __construct(TypeValidation $type = null)
    {
        $this->setTypeValidation($type);
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

    public function setTypeValidation(TypeValidation $typeValidation = null): static
    {
        $this->typeValidation = $typeValidation;

        return $this;
    }

    public function getTypeValidation(): ?TypeValidation
    {
        return $this->typeValidation;
    }

    public function getResourceId(): string
    {
        return 'Validation';
    }


    /** Fonction pour les macros du module UnicaenRenderer ************************************************************/

    /**
     * @noinspection
     * @return string
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Validation\Renderer\ValidationTheseRendererAdapter}
     */
    public function getAuteurToString() : string
    {
        $displayname = $this->getIndividu()->getNomComplet();
        return $displayname;
    }
    /**
     * @noinspection
     * @return string
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Validation\Renderer\ValidationTheseRendererAdapter}
     */
    public function getDateToString() : string
    {
        $date = $this->getHistoCreation()->format('d/m/Y à H:i');
        return $date;
    }
}
