<?php

namespace Application\Search\Filter;

interface SearchFilterValueInterface
{
    /**
     * Retourne cette instance convertie dans un format utilisable dans
     * {@see SelectSearchFilter::createValueOptionsFromData()}.
     *
     * Exemples de valeurs retournées possibles :
     * ```
     * `['value' => 'UCN', 'label' => "Unicaen"]`
     * `['value' => 'UCN', 'label' => "Unicaen", 'subtext' => "Université de Caen"]`
     * ```
     *
     * @return array
     */
    public function createSearchFilterValueOption(): array ;
}