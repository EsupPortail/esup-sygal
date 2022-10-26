<?php

namespace Application\ORM\Query\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Fonction DQL maison "atteignable()".
 * 
 * NB: le format DQL attendu est "atteignable(EntityExpression, EntityExpression)".
 * Exemples :
 * <pre>
 * $qb->andWhere("1 = atteignable(e, t)")
 * </pre>
 * 
 * En SQL, génère l'appel à la fonction Oracle APP_WORKFLOW.ATTEIGNABLE().
 */
class ToNumber extends FunctionNode
{
    public $value;
    public $format;

    /**
     * Parsing.
     *
     * NB: le format DQL attendu est "toNumber(ArithmeticPrimary, StringExpression)".
     * Exemple :
     *  toNumber(ur.code, '999999')
     *
     * @param Parser $parser
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->value = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->format = $parser->StringExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Génère le code SQL.
     * 
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf("to_number(%s, %s)", $this->value->dispatch($sqlWalker), $this->format->dispatch($sqlWalker));
    }
}