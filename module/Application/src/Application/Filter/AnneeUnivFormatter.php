<?php

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;

class AnneeUnivFormatter extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  string|string[]|int|int[] $value
     * @return string|string[]
     */
    public function filter($value, string $separator = '/')
    {
        if (! $value) {
            return $value;
        }

        $f = function($value) use ($separator) {
            $value = (int) $value;
            return $value . $separator . ($value+1);
        };

        if (is_array($value)) {
            return array_map($f, $value);
        } else {
            return $f($value);
        }
    }
}