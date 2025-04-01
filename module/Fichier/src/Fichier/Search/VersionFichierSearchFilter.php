<?php

namespace Fichier\Search;

use Application\Search\Filter\SelectSearchFilter;

class VersionFichierSearchFilter extends SelectSearchFilter
{
    public const NAME = 'version';

    protected string $name = self::NAME;
    protected string $label = "Version du fichier";
}