<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\UniteRecherche;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class UniteRechercheRepository extends DefaultEntityRepository
{

    /**
     * @return UniteRecherche[]
     */
    public function findAll()
    {
        /** @var UniteRecherche[] $unites */
        $qb = $this->getEntityManager()->getRepository(UniteRecherche::class)->createQueryBuilder("ur");
        $qb
            ->leftJoin("ur.structure", "str", "WITH", "ur.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        $unites = $qb->getQuery()->getResult();

        return $unites;
    }

    public function findByStructureId($id)
    {
        /** @var UniteRecherche $unite */
        $qb = $this->createQueryBuilder("u")
            ->addSelect("s")
            ->leftJoin("u.structure", "s")
            ->andWhere("s.id = :id")
            ->setParameter("id", $id);
        try {
            $unite = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("UniteRechercheRepository::findByStructureId(".$id.") retourne de multiples unités de recherches !");
        }

        return $unite;
    }
}