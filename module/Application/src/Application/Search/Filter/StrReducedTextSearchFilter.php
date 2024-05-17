<?php

namespace Application\Search\Filter;

class StrReducedTextSearchFilter extends TextSearchFilter
{
    protected function createTemplate(string $expr): string
    {
        if ($this->useLikeOperator) {
            $template = "lower(strReduce($expr)) like lower(strReduce(:%s))";
        } else {
            $template = "strReduce($expr) = strReduce(:%s)";
        }

        return $template;
    }
}
