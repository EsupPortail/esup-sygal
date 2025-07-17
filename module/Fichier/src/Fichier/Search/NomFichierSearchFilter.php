<?php

namespace Fichier\Search;

use UnicaenSearch\Filter\TextSearchFilter;

class NomFichierSearchFilter extends TextSearchFilter
{
    protected string $name = 'nomFichier';
    protected string $label = "Nom du fichier";

    protected bool $useLikeOperator = true;
    protected bool $useStrReduce = true;

    public function __construct(?string $label = null, ?string $name = null, array $attributes = [], mixed $defaultValue = null)
    {
        parent::__construct($label, $name, $attributes, $defaultValue);

        $this->setWhereFields(['f.nom', 'f.nomOriginal']);
    }
}