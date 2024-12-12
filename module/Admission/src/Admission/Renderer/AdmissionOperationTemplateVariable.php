<?php

namespace Admission\Renderer;

use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\AdmissionValidation;
use InvalidArgumentException;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class AdmissionOperationTemplateVariable extends AbstractTemplateVariable
{
    private AdmissionOperationInterface $operation;

    public function setOperation(AdmissionOperationInterface $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAuteurToString() : string
    {
        if ($this->operation instanceof AdmissionValidation) {
            return $this->operation->getIndividu()->getNomComplet();
        } elseif ($this->operation instanceof AdmissionAvis) {
            return (string) ($this->operation->getHistoModificateur() ?: $this->operation->getHistoCreateur());
        } else {
            throw new InvalidArgumentException("Opération de type " . get_class($this->operation) . " inattendue !");
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString() : string
    {
        $date = $this->operation->getHistoModification() ?: $this->operation->getHistoCreation();
        return $date->format('d/m/Y à H:i');
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getModificateurToString() : string
    {
        return "par ".$this->operation->getHistoModificateur();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDestructeurToString() : string
    {
        return "par ".$this->operation->getHistoDestructeur();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleType(): string
    {
        if ($this->operation instanceof AdmissionValidation) {
            return $this->operation->getTypeValidation()->getLibelle();
        } elseif ($this->operation instanceof AdmissionAvis) {
            return $this->operation->getAvis()->getAvisType()->getLibelle();
        } else {
            throw new InvalidArgumentException("Opération de type " . get_class($this->operation) . " inattendue !");
        }
    }
}