<?php

namespace Application\ORM\Query\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Fonction DQL maison "year()".
 * 
 * NB: le format DQL attendu est "year(ArithmeticPrimary)".
 * Exemples :
 * <pre>
 * $qb->andWhere("1 = year(e.attr)")
 * </pre>
 * 
 * En SQL, génère l'appel à la fonction Oracle EXTRACT(year FROM ...).
 */
class Year extends FunctionNode
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
        $sql = sprintf('EXTRACT(year FROM %s)', $this->expression->dispatch($sqlWalker));
        
        return $sql;
    }
}