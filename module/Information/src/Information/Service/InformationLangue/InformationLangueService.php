<?php

namespace Information\Service\InformationLangue;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Information\Entity\Db\InformationLangue;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InformationLangueService {
    use EntityManagerAwareTrait;

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->getEntityManager()->getRepository(InformationLangue::class)->createQueryBuilder('langue');
        return $qb;
    }

    /**
     * @param string $id
     * @return InformationLangue
     */
    public function getLangue(string $id) : InformationLangue
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('langue.id = :id')
            ->setParameter('id',$id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs InformationLangue partagent le même id [".$id."]");
        }
        return $result;
    }

    /**
     * @return InformationLangue[]
     */
    public function getLangues() : array
    {
        $qb = $this->createQueryBuilder()
            ->orderBy('langue.libelle', 'ASC');
        return $qb->getQuery()->getResult();
    }
}