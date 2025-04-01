<?php

namespace Fichier\Search;

use Application\Search\Filter\TextSearchFilter;

class NomFichierSearchFilter extends TextSearchFilter
{
    protected string $name = 'nomFichier';
    protected string $label = "Nom du fichier";

    protected bool $useLikeOperator = true;
    protected bool $useStrReduce = true;

    protected ?array $whereFields = [
        self::OPERATOR_OR => ['f.nom', 'f.nomOriginal']
    ];
}