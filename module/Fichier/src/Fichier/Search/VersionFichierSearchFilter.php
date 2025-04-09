<?php

namespace Fichier\Search;

use UnicaenSearch\Filter\SelectSearchFilter;

class VersionFichierSearchFilter extends SelectSearchFilter
{
    public const NAME = 'version';

    protected string $name = self::NAME;
    protected string $label = "Version du fichier";
}