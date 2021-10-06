<?php

namespace Application\Entity\Db;

use UnicaenDbImport\Entity\Db\AbstractSource;

/**
 * Source
 */
class Source extends AbstractSource
{
    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * @return Etablissement|null
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @since PHP 5.6.0
     * This method is called by var_dump() when dumping an object to get the properties that should be shown.
     * If the method isn't defined on an object, then all public, protected and private properties will be shown.
     *
     * @return array
     * @link  http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     */
    function __debugInfo(): array
    {
        return [
            'libelle' => $this->libelle,
        ];
    }
}