<?php

namespace Doctorant\Search;

use Application\Search\Filter\TextSearchFilter;
use Doctrine\ORM\QueryBuilder;

class DoctorantSearchFilter extends TextSearchFilter // todo : hériter de StrReducedTextSearchFilter
{
    const NAME = 'doctorant';

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        return new self("Doctorant", self::NAME);
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $filterValue = $this->getValue();
        if ($filterValue === null) {
            return;
        }

        $filterValue = trim($filterValue);

        if (is_numeric($filterValue)) {
            $qb
                ->andWhere('doctorant.sourceCode LIKE :numeroEtudiant')
                ->setParameter('numeroEtudiant', '%' . $filterValue . '%');
        } elseif (strlen($filterValue) > 1) {
            $qb->join('doctorant.individu', $alias = uniqid('individu'));
            // si 2 mots séparés par un espace sont saisis, on les interprète comme nom+prénom ou prenom+nom :
            if (count($words = array_filter(explode(' ', $filterValue))) === 2) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->andX(
                            "strReduce($alias.nomUsuel) LIKE strReduce(:word1)",
                            "strReduce($alias.prenom1) LIKE strReduce(:word2)"
                        ),
                        $qb->expr()->andX(
                            "strReduce($alias.nomUsuel) LIKE strReduce(:word2)",
                            "strReduce($alias.prenom1) LIKE strReduce(:word1)"
                        )
                    ))
                    ->setParameter('word1', '%' . $words[0] . '%')
                    ->setParameter('word2', '%' . $words[1] . '%');
            } else {
                $qb
                    ->andWhere($qb->expr()->orX(
                        "strReduce($alias.nomUsuel) LIKE strReduce(:text)",
                        "strReduce($alias.prenom1) LIKE strReduce(:text)"
                    ))
                    ->setParameter('text', '%' . $filterValue . '%');
            }
        }
    }
}