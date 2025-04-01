<?php

namespace Fichier\Search;

use Application\Search\Filter\CheckboxSearchFilter;

class IdPermanentFichierSearchFilter extends CheckboxSearchFilter
{
    protected string $name = 'idPermanent';
    protected string $label = "Id permanent";
    protected ?array $whereFields = ['f.idPermanent'];

    protected function generateComparisonTemplate(string $operand): string
    {
        return $this->getValue() ? "$operand is not null" : "$operand is null";
    }

    protected function generateParameterName(): ?string
    {
        // pas de paramètre car la comparaison utilise 'is null' ou 'is not null'
        return null;
    }
}