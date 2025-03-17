<?php

namespace Application\Search\Filter;

/**
 * @deprecated Utiliser à la place la classe {@see \Application\Search\Filter\TextSearchFilter}
 * et sa méthode {@see \Application\Search\Filter\TextSearchFilter::useStrReduce()}.
 */
class StrReducedTextSearchFilter extends TextSearchFilter
{
    protected function generateComparisonTemplate(string $operand): string
    {
        if ($this->useLikeOperator) {
            $template = "lower(strReduce($operand)) like lower(strReduce(:%s))";
        } else {
            $template = "strReduce($operand) = strReduce(:%s)";
        }

        return $template;
    }
}
