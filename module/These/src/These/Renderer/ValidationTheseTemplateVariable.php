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
        $displayname = $this->validationThese->getIndividu()->getNomComplet();
        return $displayname;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString() : string
    {
        $date = $this->validationThese->getHistoCreation()->format('d/m/Y à H:i');
        return $date;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleType() : string
    {
        return $this->validationThese->getValidation()->getTypeValidation()->getLibelle();
    }
}