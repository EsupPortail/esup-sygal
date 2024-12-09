<?php

namespace Individu\Renderer;

use Application\Filter\NomCompletFormatter;
use Individu\Entity\Db\Individu;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

/**
 * @property Individu $target
 */
class IndividuTemplateVariable extends AbstractTemplateVariable
{
    public function setIndividu(Individu $individu): void
    {
        $this->setTarget($individu);
    }

    public function getNomPatronymique(): string
    {
        return $this->target->getNomPatronymique();
    }

    public function getPrenom(): string
    {
        return $this->target->getPrenom();
    }

    public function getNomComplet(): string
    {
        $f = new NomCompletFormatter();
        $f->avecCivilite();

        return $f->filter($this->target);
    }
}