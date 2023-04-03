<?php

namespace UnicaenIdref\Domain;

abstract class AbstractIndex
{
    protected string $name;
    protected string $valueName;

    protected string $index;
    protected string $indexValue;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValueName(): string
    {
        return $this->valueName;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @param string $index
     * @return self
     */
    public function setIndex(string $index): self
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @return string
     */
    public function getIndexValue(): string
    {
        return $this->indexValue;
    }

    /**
     * @param string $indexValue
     * @return self
     */
    public function setIndexValue(string $indexValue): self
    {
        $this->indexValue = $indexValue;
        return $this;
    }
}