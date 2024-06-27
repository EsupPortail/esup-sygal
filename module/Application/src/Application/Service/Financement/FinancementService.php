<?php

namespace Application\Service\Financement;

use Application\Entity\Db\Financement;
use Application\Entity\Db\OrigineFinancement;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FinancementService
{
    use EntityManagerAwareTrait;

    public function getRepository()
    {
        $repo = $this->entityManager->getRepository(Financement::class);
        return $repo;
    }

    /**
     * @param string|null $order
     * @param bool $cacheable
     * @return OrigineFinancement[]
     */
    public function findOriginesFinancements(string $order = null, bool $cacheable = false): array
    {
        $qb = $this->getRepositoryOrigineFinancement()->createQueryBuilder('origine');

        if ($order) {
            $qb = $qb->orderBy('origine.' . $order);
        }

        $qb->setCacheable($cacheable);

        return $qb->getQuery()->getResult();
    }

    public function getOrigineFinancementByCode(string $code): OrigineFinancement
    {
        /** @var OrigineFinancement $of */
        $of = $this->getRepositoryOrigineFinancement()->findOneBy(['code' => $code]);

        return $of;
    }

    protected function getRepositoryOrigineFinancement()
    {
        return $this->getEntityManager()->getRepository(OrigineFinancement::class);
    }
    public function findFinancementsByThese(These $these): array|null
    {
        $qb = $this->getRepository()->createQueryBuilder('financement');

        $qb ->join('financement.these', 'these')->addSelect('these')
            ->andWhere('financement.these = :these')->setParameter('these', $these);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findFinancementByTheseAndOrigineFinancement(These $these, OrigineFinancement $origineFinancement): Financement|null
    {
        $qb = $this->getRepository()->createQueryBuilder('financement');

        $qb ->join('financement.these', 'these')->addSelect('these')
            ->join('financement.origineFinancement', 'origine')->addSelect('origine')
            ->andWhere('financement.these = :these')->setParameter('these', $these)
            ->andWhere('financement.origineFinancement = :origine')->setParameter('origine', $origineFinancement);

        return $qb->getQuery()->getOneOrNullResult();
    }


    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Financement $seance
     * @return Financement
     */
    public function create(Financement $financement) : Financement
    {
        try {
            $this->getEntityManager()->persist($financement);
            $this->getEntityManager()->flush($financement);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Financement]",0, $e);
        }
        return $financement;
    }

    /**
     * @param Financement $financement
     * @return Financement
     */
    public function update(Financement $financement) : Financement
    {
        try {
            $this->getEntityManager()->flush($financement);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Financement]",0, $e);
        }
        return $financement;
    }

    /**
     * @param Financement $seance
     * @return Financement
     */
    public function delete(Financement $financement) : Financement
    {
        try {
            $this->getEntityManager()->remove($financement);
            $this->getEntityManager()->flush($financement);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [Financement]",0, $e);
        }
        return $financement;
    }
}