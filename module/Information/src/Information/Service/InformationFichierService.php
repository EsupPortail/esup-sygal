<?php

namespace Information\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Information\Entity\Db\InformationFichier;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InformationFichierService {
    use EntityManagerAwareTrait;

    /**
     * @var InformationFichier $fichier
     * @return InformationFichier
     */
    public function create($fichier)
    {
        $this->getEntityManager()->persist($fichier);
        try {
            $this->getEntityManager()->flush($fichier);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de la création d'un InformationFichier", $e);
        }
        return $fichier;
    }

    /**
     * @var InformationFichier $fichier
     * @return InformationFichier
     */
    public function update($fichier)
    {
        try {
            $this->getEntityManager()->flush($fichier);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de la mise à jour d'un InformationFichier", $e);
        }
        return $fichier;
    }

    /**
     * @var InformationFichier $fichier
     */
    public function delete($fichier)
    {
        $this->getEntityManager()->remove($fichier);
        try {
            $this->getEntityManager()->flush($fichier);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de l'effacement d'un InformationFichier", $e);
        }
    }

    /**
     * @return InformationFichier[]
     */
    public function getInformationFichiers()
    {
        $qb = $this->getEntityManager()->getRepository(InformationFichier::class)->createQueryBuilder('fichier')
            ->orderBy('fichier.id')
        ;
        return $qb->getQuery()->getResult();
    }

    /**
     * @param integer $id
     * @return InformationFichier
     */
    public function getInformationFichier($id)
    {
        $qb = $this->getEntityManager()->getRepository(InformationFichier::class)->createQueryBuilder('fichier')
            ->andWhere('fichier.id = :id')
            ->setParameter('id', $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieur InformationFichier partagent le même identifiant [".$id."]", $e);
        }
        return $result;
    }
}