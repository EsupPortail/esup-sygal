<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\UniteRecherche;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;
use UnicaenImport\Entity\Db\Source;

class UniteRechercheRepository extends DefaultEntityRepository
{

    /**
     * @param Source|null $source
     * @return UniteRecherche[]
     */
    public function findAll(Source $source = null)
    {
        /** @var UniteRecherche[] $unites */
        $qb = $this->getEntityManager()->getRepository(UniteRecherche::class)->createQueryBuilder("ur");
        $qb
            ->leftJoin("ur.structure", "str", "WITH", "ur.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        if ($source !== null) {
            $qb
                ->join('ur.source', 'src', Join::WITH, 'src = :source')
                ->setParameter('source', $source);
        }

        $unites = $qb->getQuery()->getResult();

        return $unites;
    }

    /**
     * @param int $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return null|UniteRecherche
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        /** @var UniteRecherche $unite */
        $unite = $this->findOneBy(["id" => $id]);

        return $unite;
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