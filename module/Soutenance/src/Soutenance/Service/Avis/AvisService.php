<?php

namespace Soutenance\Service\Avis;

use Application\Entity\Db\Acteur;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Avis;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class AvisService {
    use EntityManagerAwareTrait;

    /**
     * @param int $id
     * @return Avis
     */
    public function getAvis($id)
    {
        $qb = $this->getEntityManager()->getRepository(Avis::class)->createQueryBuilder('avis')
            ->andWhere('avis.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs avis partagent le même identifiant ['.$id.']', $e);
        }

        return $result;
    }

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function create($avis)
    {
        $this->getEntityManager()->persist($avis);
        try {
            $this->getEntityManager()->flush($avis);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException('Un problème est survenu lors de la création de l\'avis', $e);
        }

        return $avis;
    }

    /**
     * @param Avis $avis
     * @return Avis
     */
    public function update($avis)
    {
        try {
            $this->getEntityManager()->flush($avis);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException('Un problème est survenu lors de la mise à jour de l\'avis', $e);
        }

        return $avis;
    }

    /**
     * @param Avis $avis
     */
    public function delete($avis)
    {
        $this->getEntityManager()->remove($avis);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException('Un problème est survenu lors de l\'effacement de l\'avis', $e);
        }

    }

    /**
     * @param Acteur $rapporteur
     * @return Avis
     */
    public function getAvisByRapporteur($rapporteur)
    {
        $qb = $this->getEntityManager()->getRepository(Avis::class)->createQueryBuilder('avis')
            ->andWhere('avis.these = :these')
            ->andWhere('avis.rapporteur = :rapporteur')
            ->setParameter('these', $rapporteur->getThese())
            ->setParameter('rapporteur', $rapporteur);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs avis sont associés au rapporteur ['.$rapporteur->getId().' - '.$rapporteur->getIndividu()->getNomComplet().']');
        }

        return $result;
    }
}