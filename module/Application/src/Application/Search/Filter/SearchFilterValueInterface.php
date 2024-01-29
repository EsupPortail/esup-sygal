<?php

namespace Application\Search\Filter;

interface SearchFilterValueInterface
{
    /**
     * Convertie l'instance courante dans un format utilisable par {@see SelectSearchFilter::createValueOptionsFromData()}.
     *
     * Exemples de valeurs retournées possibles :
     * ```
     * `['value' => 'UCN', 'label' => "Unicaen"]`
     * `['value' => 'UCN', 'label' => "Unicaen", 'subtext' => "Université de Caen"]`
     * ```
     *
     * @return array[]
     */
    public function createSearchFilterValueOption(): array ;
}