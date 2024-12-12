<?php

namespace Individu\Renderer;

use Application\Filter\NomCompletFormatter;
use Individu\Entity\Db\Individu;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class IndividuTemplateVariable extends AbstractTemplateVariable
{
    private Individu $individu;

    public function setIndividu(Individu $individu): void
    {
        $this->individu = $individu;
    }

    public function getNomComplet(): string
    {
        $f = new NomCompletFormatter();
        $f->avecCivilite();

        return $f->filter($this->individu);
    }
}