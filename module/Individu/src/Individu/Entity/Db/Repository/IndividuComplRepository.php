<?php

namespace Individu\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuCompl;
use UnicaenApp\Exception\RuntimeException;

class IndividuComplRepository extends DefaultEntityRepository
{
    /**
     * @param Individu $individu
     * @return IndividuCompl|null
     */
    public function findIndividuComplByIndividu(Individu $individu) : ?IndividuCompl
    {
        $qb = $this->getEntityManager()->getRepository(IndividuCompl::class)->createQueryBuilder("individucompl")
            ->join("individucompl.individu","individu")
            ->andWhere("individucompl.individu = :individu")
            ->setParameter("individu", $individu)
            ->andWhere("individucompl.histoDestruction is NULL")
        ;

        /** @var IndividuCompl $entity */
        try {
            $entity = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieurs IndividuCompl ont le même individu.");
        }
        return $entity;
    }

    /**
     * @param Individu $individu
     * @return IndividuCompl[]
     */
    public function findAllIndividuComplsByIndividu(Individu $individu) : array
    {
        $qb = $this->getEntityManager()->getRepository(IndividuCompl::class)->createQueryBuilder("individucompl")
            ->join("individucompl.individu","individu")
            ->andWhere("individucompl.individu = :individu")
            ->setParameter("individu", $individu)
            ->orderBy("individuCompl.histoCreation")
        ;

        /** @var IndividuCompl[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}