<?php

namespace Soutenance\Service\Qualite;

use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Soutenance\Entity\Qualite;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class QualiteService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/


    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function create(Qualite $qualite)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $qualite->setHistoCreateur($user);
        $qualite->setHistoCreation($date);
        $qualite->setHistoModificateur($user);
        $qualite->setHistoModification($date);

        try {
            $this->getEntityManager()->persist($qualite);
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'enregistrement en BD d'une nouvelle qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function update(Qualite $qualite)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $qualite->setHistoModificateur($user);
        $qualite->setHistoModification($date);

        try {
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function historise(Qualite $qualite)
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
        } catch(Exception $e) {
            throw new RuntimeException("Un problème est survenu lors de la récupération des données liées à l'historisation", 0 , $e);
        }

        $qualite->setHistoDestructeur($user);
        $qualite->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function restoreQualite(Qualite $qualite)
    {
        $qualite->setHistoDestructeur(null);
        $qualite->setHistoDestruction(null);

        try {
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     */
    public function delete(Qualite $qualite)
    {
        try {
            $this->getEntityManager()->remove($qualite);
            $this->getEntityManager()->flush($qualite);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'effacement en BD d'une nouvelle qualité.");
        }
    }

    /** REQUETAGE *****************************************************************************************************/

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->addSelect('libelleSupplementaire')->leftJoin('qualite.libellesSupplementaires', 'libelleSupplementaire');

        return $qb;
    }

    /** @return Qualite[] */
    public function getQualites() {
        $qb = $this->createQueryBuilder()
            ->orderBy('qualite.libelle', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getQualite($id) {
        $qb = $this->createQueryBuilder()
            ->andWhere("qualite.id = :id")
            ->setParameter("id", $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
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
        return $this->getQualite($id);
    }

    public function getQualiteByLibelle($libelle) {
        $qb = $this->createQueryBuilder()
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
        return $qb->getQuery()->getResult();
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


}