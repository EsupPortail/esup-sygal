<?php

namespace Application\ORM\Query\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Fonction DQL maison "npd()".
 * 
 * NB: le format DQL attendu est "npd(StringExpression, EntityExpression)".
 *
 * Par exemple, le DQL suivant :
 *      npd('individu', a)
 * Générera le SQL suivant :
 *      substit_npd_individu(a.*)
 */
class Npd extends FunctionNode
{
    public $type;
    public $alias;

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->type = $parser->StringExpression();
        $parser->match(Lexer::T_COMMA);
        $this->alias = $parser->EntityExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $expr1 = clone($this->type);
        $expr2 = clone($this->alias);

        return sprintf('substit_npd_%s(%s)', $expr1->dispatch($sqlWalker), $expr2->dispatch($sqlWalker));
    }
}