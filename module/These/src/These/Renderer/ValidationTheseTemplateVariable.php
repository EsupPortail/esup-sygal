<?php

namespace These\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Validation\Entity\Db\ValidationThese;

class ValidationTheseTemplateVariable extends AbstractTemplateVariable
{
    private ValidationThese $validationThese;

    public function setValidationThese(ValidationThese $validationThese): void
    {
        $this->validationThese = $validationThese;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAuteurToString() : string
    {
        return $this->validationThese->getIndividu()->getNomComplet();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString() : string
    {
        return $this->validationThese->getHistoCreation()->format('d/m/Y à H:i');
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleType() : string
    {
        return $this->validationThese->getValidation()->getTypeValidation()->getLibelle();
    }
}