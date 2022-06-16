<?php

namespace Fichier\Service\NatureFichier;

use Fichier\Entity\Db\NatureFichier;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\QueryException;
use RuntimeException;

class NatureFichierService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(NatureFichier::class);
    }

    /**
     * @param array $codes
     * @return NatureFichier[]
     */
    public function findAllByCodes(array $codes): array
    {
        $qb = $this->getRepository()->createQueryBuilder('nf')
            ->where((new Expr())->in('nf.code', $codes));

        try {
            return $qb->indexBy('nf', 'nf.code')->getQuery()->useQueryCache(true)->enableResultCache()->getResult();
        } catch (QueryException $e) {
            throw new RuntimeException("Erreur dans la requête", null, $e);
        }
    }
}