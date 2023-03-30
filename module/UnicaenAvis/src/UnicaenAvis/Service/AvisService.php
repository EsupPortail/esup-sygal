<?php

namespace UnicaenAvis\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\PersistenceEntityRepository ;
use DoctrineModule\Persistence\ProvidesObjectManager;
use RuntimeException;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Entity\Db\AvisTypeValeurComplem;
use UnicaenAvis\Entity\Db\AvisTypeValeur;
use UnicaenAvis\Entity\Db\AvisValeur;

class AvisService
{
    use ProvidesObjectManager;

    protected function getAvisTypeRepository(): EntityRepository
    {
        return $this->objectManager->getRepository(AvisType::class);
    }

    protected function getAvisValeurRepository(): EntityRepository
    {
        return $this->objectManager->getRepository(AvisValeur::class);
    }

    protected function getAvisTypeValeurRepository(): EntityRepository
    {
        return $this->objectManager->getRepository(AvisTypeValeur::class);
    }

    protected function getAvisTypeValeurComplemRepository(): EntityRepository
    {
        return $this->objectManager->getRepository(AvisTypeValeurComplem::class);
    }

    /**
     * Recherche tous les types d'avis.
     *
     * @return AvisType[]
     */
    public function findAllAvisTypes(): array
    {
        $qb = $this->getAvisTypeRepository()->createQueryBuilder('at', 'at.code')
            ->leftJoin('at.avisValeurs', 'av')->addSelect('av')
            ->addOrderBy('at.ordre, at.code');

        return $qb->getQuery()->useQueryCache(true)->enableResultCache()->getResult();
    }

    /**
     * Recherche des types d'avis par leurs codes, ordonnés selon la colonne 'ordre'.
     *
     * @param array $codes
     * @return AvisType[]
     */
    public function findAvisTypesByCodes(array $codes): array
    {
        $qb = $this->getAvisTypeRepository()->createQueryBuilder('at')
            ->where((new Expr())->in('at.code', $codes))
            ->addOrderBy('at.ordre')
        ;

        return $qb->getQuery()->useQueryCache(true)->enableResultCache()->getResult();
    }

    public function findOneAvisTypeByCode(string $code): ?AvisType
    {
        $qb = $this->getAvisTypeRepository()->createQueryBuilder('at')
            ->leftJoin('at.avisValeurs', 'av')->addSelect('av')
            ->where('at.code = :code')
            ->setParameter('code', $code)
        ;

        /** @var \UnicaenAvis\Entity\Db\AvisType $avisType */
        $avisType = $qb->getQuery()->useQueryCache(true)->enableResultCache()->getOneOrNullResult();

        return $avisType;
    }

    public function findOneAvisTypeById(string $id): AvisType
    {
        $qb = $this->getAvisTypeRepository()->createQueryBuilder('at')
            ->leftJoin('at.avisValeurs', 'av')->addSelect('av')
            ->where('at.id = :id')
            ->setParameter('id', $id);

        /** @var \UnicaenAvis\Entity\Db\AvisType $avisType */
        $avisType = $qb->getQuery()->useQueryCache(true)->enableResultCache()->getOneOrNullResult();

        return $avisType;
    }

    public function findOneAvisValeurByCode(string $code): AvisValeur
    {
        /** @var \UnicaenAvis\Entity\Db\AvisValeur $avisValeur */
        $avisValeur = $this->getAvisValeurRepository()->findOneBy(['code' => $code]);

        return $avisValeur;
    }

    public function findOneAvisTypeValeur(AvisType $avisType, AvisValeur $avisValeur): AvisTypeValeur
    {
        /** @var \UnicaenAvis\Entity\Db\AvisTypeValeur $avisTypeValeur */
        $avisTypeValeur = $this->getAvisTypeValeurRepository()->findOneBy([
            'avisType' => $avisType,
            'avisValeur' => $avisValeur,
        ]);

        return $avisTypeValeur;
    }

    /**
     * @param \UnicaenAvis\Entity\Db\AvisType $avisType
     */
    public function saveAvisType(AvisType $avisType)
    {
        $this->objectManager->beginTransaction();
        try {
            $this->objectManager->persist($avisType);
//            foreach ($avisType->getAvisComplems() as $avisComplem) {
//                $this->objectManager->persist($avisComplem);
//                $this->objectManager->flush($avisComplem);
//            }
            $this->objectManager->flush($avisType);
            $this->objectManager->commit();
        } catch (Exception $e) {
            $this->objectManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'AvisType, rollback!", null, $e);
        }
    }

    /**
     * @param \UnicaenAvis\Entity\Db\Avis $avis
     */
    public function saveAvis(Avis $avis)
    {
        $this->objectManager->beginTransaction();
        try {
            $this->objectManager->persist($avis);
            foreach ($avis->getAvisComplems() as $avisComplem) {
                $this->objectManager->persist($avisComplem);
                $this->objectManager->flush($avisComplem);
            }
            $this->objectManager->flush($avis);
            $this->objectManager->commit();
        } catch (Exception $e) {
            $this->objectManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement de l'avis, rollback!", null, $e);
        }
    }

    /**
     * @param Avis $avis
     */
    public function deleteAvis(Avis $avis)
    {
        $this->objectManager->beginTransaction();
        try {
            $this->objectManager->remove($avis);
            $this->objectManager->flush($avis);
            $this->objectManager->commit();
        } catch (Exception $e) {
            $this->objectManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression de l'avis, rollback!", 0, $e);
        }
    }
}