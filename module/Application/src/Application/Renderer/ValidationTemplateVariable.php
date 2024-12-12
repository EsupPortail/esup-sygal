<?php

namespace Application\Renderer;

use Application\Entity\Db\Validation;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class ValidationTemplateVariable extends AbstractTemplateVariable
{
    private Validation $validation;

    public function setValidation(Validation $validation): void
    {
        $this->validation = $validation;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAuteurToString() : string
    {
        $displayname = $this->validation->getIndividu()->getNomComplet();
        return $displayname;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString() : string
    {
        $date = $this->validation->getHistoCreation()->format('d/m/Y à H:i');
        return $date;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleType() : string
    {
        return $this->validation->getTypeValidation()->getLibelle();
    }
}