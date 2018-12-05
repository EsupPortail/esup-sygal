<?php

namespace Application\Entity\Db;

/**
 * Source
 */
class Source extends \UnicaenImport\Entity\Db\Source
{
    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }
}