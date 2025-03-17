<?php

namespace Application\Search\Filter;

/**
 *
 *
 * @author Unicaen
 */
class TextCriteriaSearchFilter extends TextSearchFilter
{
    protected string $name = 'text';

    protected string $criteriaName = 'textCriteria';

    protected ?string $textValue = null;

    /**
     * @var string[]
     */
    protected array $criteriaValue = [];

    /**
     * Critères sur lesquels peut porter le filtrage, à surcharger dans les classes filles.
     * @var string[]
     */
    protected array $availableCriteria;


    public function processQueryParams(array $queryParams): void
    {
        $filterValue = $this->paramFromQueryParams($queryParams);

        if (array_key_exists($this->criteriaName, $queryParams) && !empty($queryParams[$this->criteriaName])) {
            $criteria = $queryParams[$this->criteriaName];
        } else {
            $criteria = array_keys($this->availableCriteria);
        }

        $this->setTextValue($filterValue);
        $this->setCriteriaValue($criteria);

        $this->setValue($filterValue);
    }

    /**
     * @return string[]
     */
    public function getAvailableCriteria(): array
    {
        return $this->availableCriteria;
    }

    /**
     * Retourne le 'name' de l'élément de formulaire permettant de sélectionner le·s critère·s de filtrage.
     */
    public function getCriteriaName(): string
    {
        return $this->criteriaName;
    }

    /**
     * @return string|null
     */
    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    /**
     * Spécifie la valeur
     * @param string|null $textValue
     * @return self
     */
    public function setTextValue(string $textValue = null): self
    {
        $this->textValue = $textValue;
        return $this;
    }

    public function getCriteriaValue(): array
    {
        return $this->criteriaValue;
    }

    /**
     * @param string[] $criteriaValue
     * @return self
     */
    public function setCriteriaValue(array $criteriaValue): self
    {
        $this->criteriaValue = $criteriaValue;
        return $this;
    }
}
