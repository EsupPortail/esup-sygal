<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;

class StructureRepository extends DefaultEntityRepository
{
    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = parent::createQueryBuilder($alias);

        // Attention : il FAUT faire explicitement ces jointures sinon Doctrine génèrera d'office 3 select pour
        // chacune des 3 relations 'one-to-one' (structure=>etablissement, structure=>ecoleDoctorale, structure=>uniteRecherche),
        // ce qui multiplie le nombre de requêtes par 3 !
        $qb->addSelect('s_e')->leftJoin("$alias.etablissement", 's_e');
        $qb->addSelect('s_ed')->leftJoin("$alias.ecoleDoctorale", 's_ed');
        $qb->addSelect('s_ur')->leftJoin("$alias.uniteRecherche", 's_ur');

        return $qb;
    }

    public function findByText(string $text, int $limit = 100): array
    {
        if (strlen($text) < 2) return [];

        $qb = $this->createQueryBuilder('s');
        $qb->join('s.source', 'src')->addSelect('src');

        $params = [];
        foreach (array_filter(explode(' ', $text)) as $term) {
            $paramName = uniqid('t_');
            $qb->andWhere($qb->expr()->orX(
                "strReduce(s.libelle) LIKE strReduce(:$paramName)",
                "strReduce(s.sigle)   LIKE strReduce(:$paramName)",
            ));
            $params[$paramName] = '%' . trim($term) . '%';
        }
        $qb
            ->setParameters($params)
            ->andWhereNotHistorise()
            ->andWhere('s.estFermee = :false')
            ->setParameter('false', false)
            ->setMaxResults($limit);

        return $qb->getQuery()->getArrayResult();
    }
}