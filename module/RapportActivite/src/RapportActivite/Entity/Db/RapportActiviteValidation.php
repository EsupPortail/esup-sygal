<?php

namespace RapportActivite\Entity\Db;

use Application\Constants;
use Validation\Entity\Db\TypeValidation;
use DateTime;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class RapportActiviteValidation implements HistoriqueAwareInterface, ResourceInterface, RapportActiviteOperationInterface
{
    use HistoriqueAwareTrait;

    private ?int $id = null;

    private ?RapportActivite $rapport = null;

    private ?TypeValidation $typeValidation = null;

    private ?Individu $individu;

    public function __construct(?TypeValidation $type = null, ?RapportActivite $rapport = null, ?Individu $individu = null)
    {
        if ($type !== null) {
            $this->setTypeValidation($type);
        }
        if ($rapport !== null) {
            $this->setRapportActivite($rapport);
        }
        if ($individu !== null) {
            $this->setIndividu($individu);
        }
    }

    public function __toString(): string
    {
        $str = (string) $this->getTypeValidation();

        if ($date = $this->getHistoModification() ?: $this->getHistoCreation()) {
            $str .= sprintf(" (le %s par %s)",
                $date->format(Constants::DATETIME_FORMAT),
                $this->getHistoModificateur() ?: $this->getHistoCreateur());
        }

        return $str;
    }

    public function matches(RapportActiviteOperationInterface $otherOperation): bool
    {
        return
            $otherOperation instanceof self && (
                // même id non null ou même type de validation
                $this->getId() && $otherOperation->getId() && $this->getId() === $otherOperation->getId() ||
                $this->getTypeValidation() === $otherOperation->getTypeValidation()
            );
    }

    public function getTypeToString(): string
    {
        return (string) $this->getTypeValidation();
    }

    public function getHistoModification(): DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRapportActivite(RapportActivite $rapport): self
    {
        $this->rapport = $rapport;

        return $this;
    }

    public function getRapportActivite(): RapportActivite
    {
        return $this->rapport;
    }

    public function setTypeValidation(TypeValidation $typeValidation): RapportActiviteValidation
    {
        $this->typeValidation = $typeValidation;

        return $this;
    }

    public function getTypeValidation(): TypeValidation
    {
        return $this->typeValidation;
    }

    public function getIndividu(): Individu
    {
        return $this->individu;
    }

    public function setIndividu(Individu $individu): RapportActiviteValidation
    {
        $this->individu = $individu;

        return $this;
    }

    public function getValeurBool(): bool
    {
        return true;
    }

    public function getResourceId(): string
    {
        return 'RapportActiviteValidation';
    }
}
