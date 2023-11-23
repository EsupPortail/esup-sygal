<?php

namespace Application\Entity\Db;

use Structure\Entity\Db\Etablissement;
use UnicaenDbImport\Entity\Db\AbstractSource;

/**
 * Source
 */
class Source extends AbstractSource
{
    protected ?Etablissement $etablissement = null;

    /**
     * Retourne l'éventuel établissement lié.
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }
}