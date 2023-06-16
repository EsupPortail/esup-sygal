<?php

namespace Doctorant\Entity\Db\Trait;

use Doctorant\Entity\Db\Doctorant;

trait HasDoctorantTrait
{
    protected ?Doctorant $doctorant = null;

    public function setDoctorant(?Doctorant $doctorant): void
    {
        $this->doctorant = $doctorant;
    }

    public function getDoctorant(): ?Doctorant
    {
        return $this->doctorant;
    }

}