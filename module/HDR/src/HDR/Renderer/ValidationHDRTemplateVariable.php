<?php

namespace HDR\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Validation\Entity\Db\ValidationHDR;

class ValidationHDRTemplateVariable extends AbstractTemplateVariable
{
    private ValidationHDR $validationHDR;

    public function setValidationHDR(ValidationHDR $validationHDR): void
    {
        $this->validationHDR = $validationHDR;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAuteurToString() : string
    {
        return $this->validationHDR->getIndividu()->getNomComplet();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString() : string
    {
        return $this->validationHDR->getHistoCreation()->format('d/m/Y à H:i');
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleType() : string
    {
        return $this->validationHDR->getValidation()->getTypeValidation()->getLibelle();
    }
}