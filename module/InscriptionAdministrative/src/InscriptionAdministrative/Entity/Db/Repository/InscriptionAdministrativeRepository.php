<?php

namespace InscriptionAdministrative\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use RuntimeException;

class InscriptionAdministrativeRepository extends DefaultEntityRepository
{
    public function findOneById(int $id): InscriptionAdministrative|null
    {
        return $this->createQueryBuilder('ia')
            ->where('ia = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    public function findOneBySourceCode(string $sourceCode): InscriptionAdministrative|null
    {
        try {
            return $this->createQueryBuilder('ia')
                ->where('ia.sourceCode = :sourceCode')->setParameter('sourceCode', $sourceCode)
                ->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : Plusieurs IA ont le même source_code !");
        }
    }

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        return parent::createQueryBuilder($alias, $indexBy)
            ->join('ia.source', 's')->addSelect('s')
            ->join('ia.ecoleDoctorale', 'ed')->addSelect('ed')
            ->join('ed.structure', 'sed')->addSelect('sed')
            ->join('ia.inscriptionAdministrativeApprenant', 'iaa')->addSelect('iaa')
            ->leftJoin('ia.inscriptionAdministrativeDoctorant', 'iad')->addSelect('iad')
            ->leftJoin('iad.doctorant', 'iadd')->addSelect('iadd')
            ->leftJoin('iadd.individu', 'iadi')->addSelect('iadi')
            ->andWhereNotHistorise('ia');
    }
}