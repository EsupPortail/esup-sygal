<?php

namespace Application\Service\Profil;

use Application\Entity\Db\Profil;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ProfilService {
    use EntityManagerAwareTrait;

    /**
     * @return Profil[]
     */
    public function getProfils()
    {
        $qb = $this->getEntityManager()->getRepository(Profil::class)->createQueryBuilder('profil')
            ->orderBy('profil.libelle')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param integer $id
     * @return Profil
     */
    public function getProfil($id)
    {
        $qb = $this->getEntityManager()->getRepository(Profil::class)->createQueryBuilder('profil')
            ->andWhere('profil.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs profils partagent le même id.', $e);
        }
        return $result;
    }

    /**
     * @param Profil $profil
     * @return  Profil
     */
    public function create($profil)
    {
        $this->getEntityManager()->persist($profil);
        try {
            $this->getEntityManager()->flush($profil);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la création d'un Profil", $e);
        }
        return $profil;
    }

    /**
     * @param Profil $profil
     * @return  Profil
     */
    public function update($profil)
    {
        try {
            $this->getEntityManager()->flush($profil);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour d'un Profil", $e);
        }
        return $profil;
    }

    /**
     * @param Profil $profil
     */
    public function delete($profil)
    {
        $this->getEntityManager()->remove($profil);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la suppression d'un Profil", $e);
        }
    }
}