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

        if (is_array($value)) {
            $values = $value;
            return array_map(function($value) use ($separator) {
                $value = (int) $value;
                return $value . $separator . ($value+1);
            }, $values);
        } else {
            $value = (int) $value;
            return $value . $separator . substr(($value+1), 2);
        }
    }
}