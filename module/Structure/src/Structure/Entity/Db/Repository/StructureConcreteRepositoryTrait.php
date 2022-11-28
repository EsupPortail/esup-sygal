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
            ->leftJoinStructureSubstituante('structure');

        return $qb;
    }

    public function _findAll(DefaultQueryBuilder $qb): array
    {
        $qb
            ->leftJoin("structure.structuresSubstituees", "sub")->addSelect('sub')
            ->leftJoin("structure.typeStructure", "typ")->addSelect('typ')
            ->andWhere('structure.estFermee = false')
            ->andWhereStructureEstNonSubstituee('structure')
            ->orderBy("structure.libelle");

        return $qb->getQuery()->getResult();
    }

    public function _findSubstituables(DefaultQueryBuilder $qb): array
    {
        $qb
            ->addSelect("typ")
            ->leftJoin("structure.typeStructure", "typ")
            ->andWhere('structure.estFermee = false')
            ->andWhereStructureEstNonSubstituee('structure')
            ->andWhereStructureEstNonSubstituante('structure')
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
            throw new RuntimeException("Anomalie : plusieurs structures concrètes trouvées pointant vers la même structure $id");
        }
    }

    public function _findByText(DefaultQueryBuilder $qb, ?string $term): array
    {
        $qb
            ->andWhere($qb->expr()->orX(
                'strReduce(structure.libelle) LIKE strReduce(:term)',
                'strReduce(structure.sigle)   LIKE strReduce(:term)'
            ))
            ->setParameter('term', '%'.$term.'%')
            ->andWhereNotHistorise()
            ->andWhere('structure.estFermee = :false')
            ->setParameter('false', false)
            ->andWhereStructureEstNonSubstituee('structure');

        return $qb->getQuery()->getResult();
    }
}