<?php

namespace Application\Rule;

abstract class AbstractRule implements RuleInterface
{
    protected array $options;

    public function __construct(?array $options = null)
    {
        $this->options = (array)$options;
    }
}