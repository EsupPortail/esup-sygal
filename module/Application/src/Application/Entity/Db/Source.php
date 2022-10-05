<?php

namespace Application\Entity\Db;

use Structure\Entity\Db\Etablissement;
use UnicaenDbImport\Entity\Db\AbstractSource;
use UnicaenDbImport\Entity\Db\Source as DbImportSource;

/**
 * Source
 */
class Source extends AbstractSource
{
    /**
     * @var \Structure\Entity\Db\Etablissement
     */
    protected $etablissement;

    /**
     * @return \Structure\Entity\Db\Etablissement|null
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