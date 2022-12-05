<?php

namespace UnicaenAvis\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
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
        /** @var EntityRepository $repo */
        $repo = $this->objectManager->getRepository(AvisType::class);
        return $repo;
    }

    protected function getAvisValeurRepository(): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->objectManager->getRepository(AvisValeur::class);
        return $repo;
    }

    protected function getAvisTypeValeurRepository(): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->objectManager->getRepository(AvisTypeValeur::class);
        return $repo;
    }

    protected function getAvisTypeValeurComplemRepository(): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->objectManager->getRepository(AvisTypeValeurComplem::class);
        return $repo;
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

    public function findOneAvisTypeByCode(string $code): AvisType
    {
        $qb = $this->getAvisTypeRepository()->createQueryBuilder('at')
            ->where('at.code = :code')
            ->setParameter('code', $code)
        ;

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