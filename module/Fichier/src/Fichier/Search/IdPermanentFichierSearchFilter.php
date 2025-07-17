<?php

namespace Fichier\Search;

use UnicaenSearch\Filter\CheckboxSearchFilter;

class IdPermanentFichierSearchFilter extends CheckboxSearchFilter
{
    protected string $name = 'idPermanent';
    protected string $label = "Id permanent";

    protected bool $useOnlyIfTrue = false;

    public function __construct(?string $label = null, ?string $name = null, array $attributes = [], mixed $defaultValue = null)
    {
        parent::__construct($label, $name, $attributes, $defaultValue);

        $this->setWhereField('f.idPermanent');
    }

    /**
     * Change la signification du cochage de ce filtre.
     *
     * En spécifiant `false` (valeur initiale par défaut) :
     *   - le filtre décoché signifie "id permanent null",
     *   - le filtre coché signifie "id permanent non null".
     *
     * En spécifiant `true` :
     *  - le filtre décoché n'a aucun effet,
     *  - le filtre coché signifie "id permanent non null",
     */
    public function setUseOnlyIfChecked(bool $useOnlyIfTrue): static
    {
        $this->useOnlyIfTrue = $useOnlyIfTrue;
        return $this;
    }

    public function canApplyToQueryBuilder(): bool
    {
        if ($this->useOnlyIfTrue) {
            return (bool) $this->getValue();
        }

        return parent::canApplyToQueryBuilder();
    }

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