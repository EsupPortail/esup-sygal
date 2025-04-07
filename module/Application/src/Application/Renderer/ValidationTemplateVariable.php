<?php

namespace Application\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Validation\Entity\Db\ValidationHDR;
use Validation\Entity\Db\ValidationThese;

class ValidationTemplateVariable extends AbstractTemplateVariable
{
    private ValidationThese|ValidationHDR $validation;

    public function setValidation(ValidationThese|ValidationHDR $validation): void
    {
        $this->validation = $validation;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAuteurToString() : string
    {
        return $this->validation->getIndividu()->getNomComplet();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString() : string
    {
        return $this->validation->getHistoCreation()->format('d/m/Y à H:i');
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleType() : string
    {
        return $this->validation->getValidation()->getTypeValidation()->getLibelle();
    }
}