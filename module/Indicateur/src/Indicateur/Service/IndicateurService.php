<?php

namespace Indicateur\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Indicateur\Model\Indicateur;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndicateurService {
    use EntityManagerAwareTrait;

    /**
     * @return Indicateur[]
     */
    public function findAll()
    {
        $qb = $this->getEntityManager()->getRepository(Indicateur::class)->createQueryBuilder("indicateur")
            ->orderBy("indicateur.id")
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int $id
     * @return Indicateur
     */
    public function find($id)
    {
        $qb = $this->getEntityManager()->getRepository(Indicateur::class)->createQueryBuilder("indicateur")
            ->andWhere("indicateur.id = :id")
            ->setParameter("id", $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs indicateurs portent le même identifiant [".$id."].");
        }
        if (!$result) throw new RuntimeException("Aucun indicateur ne porte l'identifiant [".$id."].");

        return $result;
    }

    public function fetch($id) {
        $sql = "SELECT * FROM MV_INDICATEUR_".$id;

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * @param Indicateur $indicateur
     * @return Indicateur
     */
    public function toggleActivite($indicateur)
    {
        $indicateur->setActif( !$indicateur->isActif() );
        try {
            $this->getEntityManager()->flush($indicateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un erreur s'est produit lors du changement d'activité de l'indicateur [".$indicateur->getId()."].");
        }
        return $indicateur;
    }

    /**
     * @param Indicateur $indicateur
     * @return Indicateur
     */
    public function createIndicateur($indicateur)
    {
        $this->getEntityManager()->persist($indicateur);
        try {
            $this->getEntityManager()->flush($indicateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un erreur s'est produit lors de l'enregistrement d'un nouvel indicateur");
        }

        return $indicateur;
    }

    /**
     * @param Indicateur $indicateur
     * @return Indicateur
     */
    public function updateIndicateur($indicateur)
    {
        try {
            $this->getEntityManager()->flush($indicateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un erreur s'est produit lors de la mise à jour d'un indicateur");
        }

        return $indicateur;
    }

    /**
     * @param Indicateur $indicateur
     */
    public function createMaterialzedView($indicateur) {

        $requete = $indicateur->getRequete();
        $requete = str_replace(';',' ', $requete);
        $requete = str_replace('/',' ', $requete);

        $SQL  = 'CREATE MATERIALIZED VIEW MV_INDICATEUR_'.$indicateur->getId(). ' ';
        $SQL .= 'REFRESH ON DEMAND ';
        $SQL .= 'START WITH SYSDATE NEXT SYSDATE + 1 as ';
        $SQL .= $requete;

        try {
            $stmt = $this->entityManager->getConnection()->prepare($SQL);
        } catch (DBALException $e) {
            throw new RuntimeException("Un problème s'est produit lors de création de la vue matérialisée.");
        }
        $stmt->execute(null);
    }

    /**
     * @param Indicateur $indicateur
     */
    public function dropMaterialzedView($indicateur) {

        $SQL = 'DROP MATERIALIZED VIEW  MV_INDICATEUR_'.$indicateur->getId();
        try {
            $stmt = $this->entityManager->getConnection()->prepare($SQL);
        } catch (DBALException $e) {
            throw new RuntimeException("Un problème s'est produit lors de destruction de la vue matérialisée.");
        }
        $stmt->execute(null);
    }
    /**
     * @param Indicateur $indicateur
     */
    public function refreshMaterializedView($indicateur) {

//        $SQL  = "BEGIN ";
//        $SQL .= "   DBMS_MVIEW.REFRESH('MV_INDICATEUR_1', 'C'); ";
//        $SQL .= "END";
//        try {
//            $stmt = $this->entityManager->getConnection()->prepare($SQL);
//        } catch (DBALException $e) {
//            throw new RuntimeException("Un problème s'est produit lors du rafraichissement de la vue matérialisée.");
//        }
//        $stmt->execute(null);
        $this->dropMaterialzedView($indicateur);
        $this->createMaterialzedView($indicateur);
    }

    /**
     * @param Indicateur $indicateur
     */
    public function destroyIndicateur($indicateur)
    {

        $this->dropMaterialzedView($indicateur);

        $this->getEntityManager()->remove($indicateur);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la destruction de l'indicateur.");
        }
    }

}