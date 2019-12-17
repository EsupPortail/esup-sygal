<?php

namespace Soutenance\Service\Qualite;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Qualite;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class QualiteService {
    use EntityManagerAwareTrait;

    /** @return Qualite[] */
    public function getQualites() {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->orderBy("qualite.libelle")
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function getQualiteById($id) {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->andWhere("qualite.id = :id")
            ->setParameter("id", $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }

    public function getQualiteByLibelle($libelle) {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->addSelect('libelleSupplementaire')->leftJoin('qualite.libellesSupplementaires', 'libelleSupplementaire')
            ->andWhere("qualite.libelle = :libelle OR libelleSupplementaire.libelle = :libelle")
            ->setParameter("libelle", $libelle);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }


    public function findAllQualites()
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder('qualite')
            ->orderBy('qualite.rang, qualite.libelle');
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function createQualite($qualite)
    {
        $this->getEntityManager()->persist($qualite);
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'enregistrement en BD d'une nouvelle qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function updateQualite($qualite)
    {
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     */
    public function removeQualite($qualite)
    {
        $this->getEntityManager()->remove($qualite);
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'effacement en BD d'une nouvelle qualité.");
        }
    }

    /**
     * @return array
     */
    public function getQualitesAsGroupOptions()
    {
        $listings = [];
        $qualites = $this->getQualites();
        foreach ($qualites as $qualite) {
            $listings[$qualite->getRang()][] = $qualite;
        }

        $result = [];
        foreach ($listings as $rang => $qualites) {
            $options = [];
            foreach ($qualites as $qualite) {
                $this_option = [
                    'value' => $qualite->getId(),
                    'label' => $qualite->getLibelle(),
                ];
                $options[] = $this_option;
            }
            $this_group = [
                'label' => $rang,
                'options' => $options,
            ];
            $result[] = $this_group;
        }
        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $paramName
     * @return Qualite
     */
    public function getRequestedQualite($controller, $paramName = 'qualite')
    {
        $id = $controller->params()->fromRoute($paramName);
        $qualite = $this->getQualiteById($id);

        return $qualite;
    }
}