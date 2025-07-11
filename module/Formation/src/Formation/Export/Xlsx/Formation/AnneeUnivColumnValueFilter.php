<?php

namespace Formation\Export\Xlsx\Formation;

use UnicaenDbImport\Filter\ColumnValue\AbstractColumnValueFilter;

class AnneeUnivColumnValueFilter extends AbstractColumnValueFilter
{
    public function __toString(): string
    {
        return "Transforme 'N' en 'N/N+1'";
    }

    public function filter($value): array
    {
        $annee = $value[$this->column];

        $value[$this->column] = $annee . '/' . ((int)$annee + 1);

        return $value;
    }
}