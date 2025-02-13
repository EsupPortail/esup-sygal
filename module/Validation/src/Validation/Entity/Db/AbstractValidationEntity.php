<?php

namespace Validation\Entity\Db;

use Application\Constants;
use DateTime;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

abstract class AbstractValidationEntity implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;

    protected ?int $id = null;
    protected Validation $validation;
    protected ?Individu $individu = null;

    public function __construct(Validation $validation, Individu|null $individu = null)
    {
        $this->setValidation($validation);
        $this->setIndividu($individu);
    }

    public function __toString(): string
    {
        return sprintf("Validation du %s par %s",
            $this->getHistoCreation()->format(Constants::DATETIME_FORMAT),
            $this->getHistoCreateur());
    }

    public function getHistoModification(): ?DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setValidation(Validation $validation): static
    {
        $this->validation = $validation;
        return $this;
    }

    public function getValidation(): Validation
    {
        return $this->validation;
    }

    public function getIndividu(): Individu
    {
        return $this->individu;
    }

    public function setIndividu(?Individu $individu): static
    {
        $this->individu = $individu;

        return $this;
    }

    abstract public function getResourceId(): string;



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
