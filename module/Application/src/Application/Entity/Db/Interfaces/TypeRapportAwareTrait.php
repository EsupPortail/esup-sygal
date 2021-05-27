<?php

namespace Application\Entity\Db\Interfaces;

use Application\Entity\Db\TypeRapport;

trait TypeRapportAwareTrait
{
    /**
     * @var TypeRapport
     */
    protected $typeRapport;

    /**
     * @param TypeRapport $typeRapport
     * @return self
     */
    public function setTypeRapport(TypeRapport $typeRapport): self
    {
        $this->typeRapport = $typeRapport;
        return $this;
    }
}