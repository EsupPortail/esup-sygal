<?php

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

class AnneeUnivFormatter extends AbstractFilter
{
    protected $separator = '/';

    /**
     * Returns the result of filtering $value
     *
     * @param  string|string[]|int|int[] $annee
     * @return string|string[]
     */
    public function filter($annee)
    {
        if (! $annee) {
            return $annee;
        }

        if (is_array($annee)) {
            $annees = $annee;
            return array_map(function($annee) {
                $annee = (int) $annee;
                return $annee . '/' . ($annee+1);
            }, $annees);
        } else {
            $annee = (int) $annee;
            return $annee . '/' . ($annee+1);
        }
    }
}