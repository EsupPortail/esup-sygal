<?php

namespace Application\ORM\Query\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Fonction DQL maison "strReduce()".
 * 
 * NB: le format DQL attendu est "strReduce(ArithmeticPrimary)".
 * Exemples :
 * <pre>$qb->andWhere("strReduce(e.libelle) LIKE strReduce(:text)")->setParameter('text', "%météo%")</pre>
 * 
 * En SQL, génère l'appel à la fonction STR_REDUCE(...).
 */
class StrReduce extends FunctionNode
{
    public $expression;

    /**
     * @param Parser $parser
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Génère le code SQL.
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('STR_REDUCE(%s)', $this->expression->dispatch($sqlWalker));
    }
}