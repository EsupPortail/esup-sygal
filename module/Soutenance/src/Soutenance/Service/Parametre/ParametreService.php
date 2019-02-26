<?php

namespace Soutenance\Service\Parametre;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Parametre;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ParametreService {
    use EntityManagerAwareTrait;

    /**
     * @return Parametre[]
     */
    public function getParametres()
    {
        $qb = $this->getEntityManager()->getRepository(Parametre::class)->createQueryBuilder('parametre')
            ->orderBy('parametre.id', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param integer $id
     * @return Parametre
     */
    public function getParametre($id)
    {
        $qb = $this->getEntityManager()->getRepository(Parametre::class)->createQueryBuilder('parametre')
            ->andWhere('parametre.id = :id')
            ->setParameter('id', $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Parametre partagent le même identifiant [".$id."]", $e);
        }
        return $result;
    }

    /**
     * @param string $code
     * @return Parametre
     */
    public function getParametreByCode($code)
    {
        $qb = $this->getEntityManager()->getRepository(Parametre::class)->createQueryBuilder('parametre')
            ->andWhere('parametre.code = :code')
            ->setParameter('code', $code)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Parametre partagent le même code [".$code."]", $e);
        }
        return $result;
    }

    /**
     * @param Parametre $parametre
     * @return Parametre
     */
    public function update(Parametre $parametre)
    {
        try {
            $this->getEntityManager()->flush($parametre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'un parametre", $e);
        }
        return $parametre;
    }
}