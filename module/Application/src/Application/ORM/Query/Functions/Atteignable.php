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
class Atteignable extends FunctionNode
{
    public $aliasEtape;
    public $aliasThese;
    public $dateObservation;

    /**
     * Parsing.
     * 
     * NB: le format DQL attendu est "atteignable(EntityExpression, EntityExpression)".
     * Exemple :
     *  atteignable(e, t)
     *
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->aliasEtape = $parser->EntityExpression();
        $parser->match(Lexer::T_COMMA);
        $this->aliasThese = $parser->EntityExpression();
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
        $expr1 = clone($this->aliasEtape);
        $expr2 = clone($this->aliasThese);
        
        $expr1->field = 'id';
        $expr2->field = 'id';
        
        $sql = sprintf('APP_WORKFLOW.ATTEIGNABLE(%s, %s)',
            $expr1->dispatch($sqlWalker),
            $expr2->dispatch($sqlWalker)
        );
        
        return $sql;
    }
}