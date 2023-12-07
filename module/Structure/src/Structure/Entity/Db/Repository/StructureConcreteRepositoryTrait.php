<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @var DefaultEntityRepository $this
 */
trait StructureConcreteRepositoryTrait
{
    public function _createQueryBuilder(string $alias): DefaultQueryBuilder
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = parent::createQueryBuilder($alias);

        $qb
            ->addSelect('structure')
            ->join("$alias.structure", 'structure')
            ->addSelect('src')
            ->join("$alias.source", 'src');

        // Attention : il FAUT faire explicitement ces jointures sinon Doctrine génèrera d'office 3 select pour
        // chacune des 3 relations 'one-to-one' (structure=>etablissement, structure=>ecoleDoctorale, structure=>uniteRecherche),
        // ce qui multiplie le nombre de requêtes par 3 !
        $qb->addSelect('s_e')->leftJoin('structure.etablissement', 's_e');
        $qb->addSelect('s_ed')->leftJoin('structure.ecoleDoctorale', 's_ed');
        $qb->addSelect('s_ur')->leftJoin('structure.uniteRecherche', 's_ur');

        return $qb;
    }

    public function _findAll(DefaultQueryBuilder $qb): array
    {
        $qb
            ->leftJoin("structure.typeStructure", "typ")->addSelect('typ')
            ->andWhere('structure.estFermee = false')
            ->orderBy("structure.libelle");

        return $qb->getQuery()->getResult();
    }

    public function _findByStructureId(DefaultQueryBuilder $qb, $id)
    {
        $qb
            ->andWhere("structure.id = :structureId")
            ->setParameter("structureId", $id);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : plusieurs structures concrètes non historisées trouvées pointant vers la même structure $id");
        }
    }

    /**
     * @return array[]
     */
    public function _findByText(DefaultQueryBuilder $qb, string $text): array
    {
        if (strlen($text) < 2) return [];

        $params = [];
        foreach (array_filter(explode(' ', $text)) as $term) {
            $paramName = uniqid('t_');
            $qb->andWhere($qb->expr()->orX(
                "strReduce(structure.libelle) LIKE strReduce(:$paramName)",
                "strReduce(structure.sigle)   LIKE strReduce(:$paramName)",
            ));
            $params[$paramName] = '%' . trim($term) . '%';
        }
        $qb
            ->setParameters($params)
            ->andWhereNotHistorise()
            ->andWhere('structure.estFermee = :false')
            ->setParameter('false', false);

        return $qb->getQuery()->getArrayResult();
    }
}