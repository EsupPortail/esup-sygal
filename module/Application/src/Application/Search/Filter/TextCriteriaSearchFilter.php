<?php

namespace Application\Search\Filter;

/**
 *
 *
 * @author Unicaen
 */
class TextCriteriaSearchFilter extends TextSearchFilter
{
    const NAME_text = 'text';
    const NAME_criteria = 'textCriteria';

    /**
     * @var string
     */
    protected $textValue;

    /**
     * @var string[]
     */
    protected $criteriaValue = [];

    /**
     * Critères possibles sur lesquels faire porter la recherche sur texte libre.
     * @var string[]
     */
    protected $availableCriteria = [];

    /**
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams)
    {
        $filterValue = $this->paramFromQueryParams($queryParams);

        if (array_key_exists(self::NAME_criteria, $queryParams) && !empty($queryParams[self::NAME_criteria])) {
            $criteria = $queryParams[self::NAME_criteria];
        } else {
            $criteria = array_keys($this->availableCriteria);
        }

        $this->setTextValue($filterValue);
        $this->setCriteriaValue($criteria);
//        $this->setValue([
//            'text' => $filterValue,
//            'criteria' => $criteria,
//        ]);
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
     * @return string|null
     */
    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    /**
     * @param string|null $textValue
     * @return self
     */
    public function setTextValue(string $textValue = null): self
    {
        $this->textValue = $textValue;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getCriteriaValue(): array
    {
        return (array) $this->criteriaValue;
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
