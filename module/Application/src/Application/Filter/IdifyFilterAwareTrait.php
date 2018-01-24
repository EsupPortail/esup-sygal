<?php

namespace Application\Filter;

trait IdifyFilterAwareTrait
{
    /**
     * @var IdifyFilter
     */
    private $idifyFilter;

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function idify($value)
    {
        if (null === $this->idifyFilter) {
            $this->idifyFilter = new IdifyFilter();
        }

        return $this->idifyFilter->filter($value);
    }
}